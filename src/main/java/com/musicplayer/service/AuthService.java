package com.musicplayer.service;

import com.auth0.jwt.JWT;
import com.auth0.jwt.algorithms.Algorithm;
import com.auth0.jwt.interfaces.DecodedJWT;
import com.musicplayer.config.AppConfig;
import com.musicplayer.db.Database;
import com.musicplayer.model.User;
import org.mindrot.jbcrypt.BCrypt;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpSession;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.Instant;
import java.time.LocalDateTime;
import java.util.Date;
import java.util.Optional;
import java.util.UUID;

public final class AuthService {
    private static final Algorithm ALGORITHM = Algorithm.HMAC256(AppConfig.get("JWT_SECRET", "musicplayer_jwt_secret_key_change_this_in_production"));
    private static final String PHP_BCRYPT_PREFIX = "$2y$";
    private static final String JAVA_BCRYPT_PREFIX = "$2a$";

    private AuthService() {
    }

    public static Optional<User> authenticate(String login, String password) throws SQLException {
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT id, username, email, password_hash, role, profile_picture FROM users WHERE username = ? OR email = ?")) {
            statement.setString(1, login);
            statement.setString(2, login);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (!resultSet.next()) {
                    return Optional.empty();
                }
                String hash = normalizeBcryptHash(resultSet.getString("password_hash"));
                if (!BCrypt.checkpw(password, hash)) {
                    return Optional.empty();
                }
                return Optional.of(new User(
                    resultSet.getInt("id"),
                    resultSet.getString("username"),
                    resultSet.getString("email"),
                    resultSet.getString("role"),
                    resultSet.getString("profile_picture")
                ));
            }
        }
    }

    public static void login(HttpServletRequest request, User user) {
        HttpSession oldSession = request.getSession(false);
        if (oldSession != null) {
            oldSession.invalidate();
        }
        HttpSession session = request.getSession(true);
        session.setAttribute("user_id", user.id());
        session.setAttribute("username", user.username());
        session.setAttribute("user_role", user.role());
    }

    public static void logout(HttpServletRequest request) {
        HttpSession session = request.getSession(false);
        if (session != null) {
            session.invalidate();
        }
    }

    public static String generateToken(User user) {
        Instant now = Instant.now();
        return JWT.create()
            .withSubject(String.valueOf(user.id()))
            .withClaim("name", user.username())
            .withClaim("role", user.role())
            .withIssuedAt(Date.from(now))
            .withExpiresAt(Date.from(now.plusSeconds(AppConfig.getInt("app.jwtExpirySeconds", 604800))))
            .sign(ALGORITHM);
    }

    public static Optional<User> currentUser(HttpServletRequest request) throws SQLException {
        Integer sessionUserId = sessionUserId(request);
        if (sessionUserId != null) {
            return loadUserById(sessionUserId);
        }
        String token = bearerToken(request);
        if (token == null) {
            return Optional.empty();
        }
        DecodedJWT jwt = JWT.require(ALGORITHM).build().verify(token);
        return loadUserById(Integer.parseInt(jwt.getSubject()));
    }

    public static Integer sessionUserId(HttpServletRequest request) {
        HttpSession session = request.getSession(false);
        if (session == null) {
            return null;
        }
        Object value = session.getAttribute("user_id");
        return value instanceof Integer ? (Integer) value : null;
    }

    public static boolean isAdmin(HttpServletRequest request) throws SQLException {
        Optional<User> user = currentUser(request);
        return user.isPresent() && "admin".equals(user.get().role());
    }

    public static Optional<User> loadUserById(int id) throws SQLException {
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT id, username, email, role, profile_picture FROM users WHERE id = ?")) {
            statement.setInt(1, id);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (!resultSet.next()) {
                    return Optional.empty();
                }
                return Optional.of(new User(
                    resultSet.getInt("id"),
                    resultSet.getString("username"),
                    resultSet.getString("email"),
                    resultSet.getString("role"),
                    resultSet.getString("profile_picture")
                ));
            }
        }
    }

    public static Optional<User> loadUserByEmail(String email) throws SQLException {
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT id, username, email, role, profile_picture FROM users WHERE email = ?")) {
            statement.setString(1, email);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (!resultSet.next()) {
                    return Optional.empty();
                }
                return Optional.of(new User(
                    resultSet.getInt("id"),
                    resultSet.getString("username"),
                    resultSet.getString("email"),
                    resultSet.getString("role"),
                    resultSet.getString("profile_picture")
                ));
            }
        }
    }

    public static String createPasswordResetToken(int userId) throws SQLException {
        String token = UUID.randomUUID().toString().replace("-", "") + UUID.randomUUID().toString().replace("-", "");
        try (Connection connection = Database.getConnection()) {
            try (PreparedStatement cleanup = connection.prepareStatement("DELETE FROM password_reset_tokens WHERE user_id = ? OR expires_at < NOW()")) {
                cleanup.setInt(1, userId);
                cleanup.executeUpdate();
            }
            try (PreparedStatement statement = connection.prepareStatement(
                "INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())"
            )) {
                statement.setInt(1, userId);
                statement.setString(2, token);
                statement.executeUpdate();
            }
        }
        return token;
    }

    public static Optional<User> verifyPasswordResetToken(String token) throws SQLException {
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement(
                 "SELECT u.id, u.username, u.email, u.role, u.profile_picture FROM password_reset_tokens prt JOIN users u ON u.id = prt.user_id WHERE prt.token = ? AND prt.expires_at > NOW()"
             )) {
            statement.setString(1, token);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (!resultSet.next()) {
                    return Optional.empty();
                }
                return Optional.of(new User(
                    resultSet.getInt("id"),
                    resultSet.getString("username"),
                    resultSet.getString("email"),
                    resultSet.getString("role"),
                    resultSet.getString("profile_picture")
                ));
            }
        }
    }

    public static boolean resetPassword(String token, String password) throws SQLException {
        Optional<User> user = verifyPasswordResetToken(token);
        if (user.isEmpty()) {
            return false;
        }
        try (Connection connection = Database.getConnection()) {
            connection.setAutoCommit(false);
            try (PreparedStatement updateUser = connection.prepareStatement("UPDATE users SET password_hash = ? WHERE id = ?");
                 PreparedStatement deleteToken = connection.prepareStatement("DELETE FROM password_reset_tokens WHERE token = ?")) {
                updateUser.setString(1, BCrypt.hashpw(password, BCrypt.gensalt(10)));
                updateUser.setInt(2, user.get().id());
                updateUser.executeUpdate();
                deleteToken.setString(1, token);
                deleteToken.executeUpdate();
                connection.commit();
                return true;
            } catch (SQLException ex) {
                connection.rollback();
                throw ex;
            } finally {
                connection.setAutoCommit(true);
            }
        }
    }

    private static String bearerToken(HttpServletRequest request) {
        String header = request.getHeader("Authorization");
        if (header != null && header.startsWith("Bearer ")) {
            return header.substring(7);
        }
        return null;
    }

    private static String normalizeBcryptHash(String hash) {
        if (hash == null) {
            return null;
        }
        if (hash.startsWith(PHP_BCRYPT_PREFIX)) {
            return JAVA_BCRYPT_PREFIX + hash.substring(PHP_BCRYPT_PREFIX.length());
        }
        return hash;
    }
}
