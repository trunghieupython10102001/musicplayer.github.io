package com.musicplayer.web;

import com.musicplayer.config.AppConfig;
import com.musicplayer.db.Database;
import com.musicplayer.model.User;
import com.musicplayer.service.AuthService;
import com.musicplayer.service.MailService;
import com.musicplayer.util.AppUtil;
import com.musicplayer.util.RequestUtil;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.Part;
import org.mindrot.jbcrypt.BCrypt;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.UUID;
import jakarta.mail.MessagingException;

@MultipartConfig(fileSizeThreshold = 1024 * 1024, maxFileSize = 100 * 1024 * 1024, maxRequestSize = 120 * 1024 * 1024)
public class ApiServlet extends BaseServlet {
    @Override
    protected void service(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setHeader("Access-Control-Allow-Origin", "*");
        response.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        response.setHeader("Access-Control-Allow-Headers", "Content-Type, Authorization");
        if ("OPTIONS".equalsIgnoreCase(request.getMethod())) {
            response.setStatus(200);
            return;
        }
        super.service(request, response);
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        dispatch(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        dispatch(request, response);
    }

    @Override
    protected void doPut(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        dispatch(request, response);
    }

    @Override
    protected void doDelete(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        dispatch(request, response);
    }

    private void dispatch(HttpServletRequest request, HttpServletResponse response) throws IOException, ServletException {
        String path = Optional.ofNullable(request.getPathInfo()).orElse("");
        try {
            switch (path) {
                case "/auth/login" -> login(request, response);
                case "/auth/register" -> register(request, response);
                case "/auth/check" -> authCheck(request, response);
                case "/auth/logout" -> logout(request, response);
                case "/auth/forgot-password" -> forgotPassword(request, response);
                case "/auth/reset-password" -> resetPassword(request, response);
                case "/songs/list" -> listSongs(request, response);
                case "/songs/search" -> searchSongs(request, response);
                case "/songs/get" -> getSong(request, response);
                case "/songs/play" -> logPlay(request, response);
                case "/songs/update" -> updateSong(request, response);
                case "/songs/delete" -> deleteSong(request, response);
                case "/playlists/list" -> listPlaylists(request, response);
                case "/playlists/get" -> getPlaylist(request, response);
                case "/playlists/create" -> createPlaylist(request, response);
                case "/playlists/update" -> updatePlaylist(request, response);
                case "/playlists/delete" -> deletePlaylist(request, response);
                case "/playlists/add-song" -> addSongToPlaylist(request, response);
                case "/playlists/remove-song" -> removeSongFromPlaylist(request, response);
                case "/favorites/list" -> listFavorites(request, response);
                case "/favorites/add" -> addFavorite(request, response);
                case "/favorites/remove" -> removeFavorite(request, response);
                case "/albums/list" -> listAlbums(request, response);
                case "/albums/songs" -> listAlbumSongs(request, response);
                case "/stats/most-played" -> mostPlayed(request, response);
                case "/stats/most-liked" -> mostLiked(request, response);
                case "/admin/stats" -> adminStats(request, response);
                case "/admin/upload" -> uploadSong(request, response);
                default -> error(response, 404, "Endpoint not found");
            }
        } catch (SQLException ex) {
            throw new ServletException(ex);
        }
    }

    private Optional<User> requireUser(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = AuthService.currentUser(request);
        if (user.isEmpty()) {
            error(response, 401, "Authentication required");
        }
        return user;
    }

    private boolean requireAdmin(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return false;
        }
        if (!"admin".equals(user.get().role())) {
            error(response, 403, "Admin access required");
            return false;
        }
        return true;
    }

    private void login(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!"POST".equalsIgnoreCase(request.getMethod())) {
            error(response, 405, "Method not allowed");
            return;
        }
        Map<String, Object> input = json(request);
        String login = RequestUtil.str(input.get("login"));
        String password = RequestUtil.str(input.get("password"));
        if (login.isBlank() || password.isBlank()) {
            error(response, 400, "Username/email and password are required");
            return;
        }
        Optional<User> user = AuthService.authenticate(login, password);
        if (user.isEmpty()) {
            error(response, 401, "Invalid credentials");
            return;
        }
        AuthService.login(request, user.get());
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("token", AuthService.generateToken(user.get()));
        data.put("user_id", user.get().id());
        data.put("username", user.get().username());
        data.put("email", user.get().email());
        data.put("role", user.get().role());
        data.put("profile_picture", user.get().profilePicture());
        ok(response, "Login successful", data);
    }

    private void register(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!"POST".equalsIgnoreCase(request.getMethod())) {
            error(response, 405, "Method not allowed");
            return;
        }
        Map<String, Object> input = json(request);
        String username = RequestUtil.str(input.get("username"));
        String email = RequestUtil.str(input.get("email"));
        String password = RequestUtil.str(input.get("password"));
        if (username.isBlank() || email.isBlank() || password.isBlank()) {
            error(response, 400, "All fields are required");
            return;
        }
        if (username.length() < 3 || username.length() > 50) {
            error(response, 400, "Username must be between 3 and 50 characters");
            return;
        }
        if (!email.matches("^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$")) {
            error(response, 400, "Invalid email format");
            return;
        }
        if (password.length() < 6) {
            error(response, 400, "Password must be at least 6 characters");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            if (exists(connection, "SELECT 1 FROM users WHERE username = ?", username)) {
                error(response, 409, "Username already taken");
                return;
            }
            if (exists(connection, "SELECT 1 FROM users WHERE email = ?", email)) {
                error(response, 409, "Email already registered");
                return;
            }
            try (PreparedStatement statement = connection.prepareStatement(
                "INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, 'user', NOW())",
                Statement.RETURN_GENERATED_KEYS
            )) {
                statement.setString(1, username);
                statement.setString(2, email);
                statement.setString(3, BCrypt.hashpw(password, BCrypt.gensalt(10)));
                statement.executeUpdate();
                try (ResultSet keys = statement.getGeneratedKeys()) {
                    int id = keys.next() ? keys.getInt(1) : 0;
                    created(response, "Registration successful", Map.of("user_id", id, "username", username, "email", email));
                }
            }
        }
    }

    private void authCheck(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = AuthService.currentUser(request);
        if (user.isEmpty()) {
            ok(response, "User is not logged in", Map.of("logged_in", false));
            return;
        }
        Map<String, Object> userData = new LinkedHashMap<>();
        userData.put("id", user.get().id());
        userData.put("username", user.get().username());
        userData.put("email", user.get().email());
        userData.put("role", user.get().role());
        userData.put("profile_picture", user.get().profilePicture());

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("logged_in", true);
        data.put("user", userData);
        ok(response, "User is logged in", data);
    }

    private void logout(HttpServletRequest request, HttpServletResponse response) throws IOException {
        AuthService.logout(request);
        ok(response, "Logout successful", Map.of());
    }

    private void forgotPassword(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!"POST".equalsIgnoreCase(request.getMethod())) {
            error(response, 405, "Method not allowed");
            return;
        }
        Map<String, Object> input = json(request);
        String email = RequestUtil.str(input.get("email"));
        if (email.isBlank()) {
            error(response, 400, "Email is required");
            return;
        }
        if (!email.matches("^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$")) {
            error(response, 400, "Invalid email format");
            return;
        }
        Optional<User> user = AuthService.loadUserByEmail(email);
        if (user.isPresent()) {
            String token = AuthService.createPasswordResetToken(user.get().id());
            String resetUrl = AppConfig.get("app.baseUrl", "http://localhost:8080") + "/reset-password.php?token=" + token;
            try {
                MailService.sendPasswordResetEmail(user.get().email(), user.get().username(), resetUrl);
            } catch (MessagingException ex) {
                getServletContext().log("Failed to send password reset email for " + user.get().email() + ": " + ex.getMessage(), ex);
                error(response, 500, "Failed to send reset email");
                return;
            }
        }
        ok(response, "If an account exists with this email, a reset link has been sent", Map.of("email_sent", true));
    }

    private void resetPassword(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!"POST".equalsIgnoreCase(request.getMethod())) {
            error(response, 405, "Method not allowed");
            return;
        }
        Map<String, Object> input = json(request);
        String token = RequestUtil.str(input.get("token"));
        String password = RequestUtil.str(input.get("password"));
        if (token.isBlank() || password.isBlank()) {
            error(response, 400, "Token and password are required");
            return;
        }
        if (password.length() < 6) {
            error(response, 400, "Password must be at least 6 characters");
            return;
        }
        if (!AuthService.resetPassword(token, password)) {
            error(response, 400, "Invalid or expired reset token");
            return;
        }
        ok(response, "Password has been reset successfully. You can now login with your new password.", Map.of());
    }

    private void listSongs(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        int page = Math.max(1, RequestUtil.intValue(Optional.ofNullable(request.getParameter("page")).orElse("1")));
        int limit = Math.max(1, Math.min(100, RequestUtil.intValue(Optional.ofNullable(request.getParameter("limit")).orElse("20"))));
        String genre = Optional.ofNullable(request.getParameter("genre")).orElse("").trim();
        int offset = (page - 1) * limit;
        try (Connection connection = Database.getConnection()) {
            int total = countSongs(connection, genre, null);
            String sql = "SELECT id, title, artist, album, duration, file_path, cover_image, genre, release_year, play_count FROM songs"
                + (genre.isBlank() ? "" : " WHERE genre = ?") + " ORDER BY title ASC LIMIT ? OFFSET ?";
            try (PreparedStatement statement = connection.prepareStatement(sql)) {
                int index = 1;
                if (!genre.isBlank()) {
                    statement.setString(index++, genre);
                }
                statement.setInt(index++, limit);
                statement.setInt(index, offset);
                List<Map<String, Object>> songs = songList(statement.executeQuery());
                ok(response, "Songs retrieved successfully", Map.of("songs", songs, "pagination", AppUtil.paginate(total, limit, page)));
            }
        }
    }

    private void searchSongs(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        String q = Optional.ofNullable(request.getParameter("q")).orElse("").trim();
        if (q.isBlank()) {
            error(response, 400, "Search query is required");
            return;
        }
        int page = Math.max(1, RequestUtil.intValue(Optional.ofNullable(request.getParameter("page")).orElse("1")));
        int limit = Math.max(1, Math.min(100, RequestUtil.intValue(Optional.ofNullable(request.getParameter("limit")).orElse("20"))));
        int offset = (page - 1) * limit;
        String pattern = "%" + q + "%";
        try (Connection connection = Database.getConnection()) {
            int total = countSongs(connection, null, pattern);
            try (PreparedStatement statement = connection.prepareStatement("SELECT id, title, artist, album, duration, file_path, cover_image, genre, release_year, play_count FROM songs WHERE title LIKE ? OR artist LIKE ? OR album LIKE ? ORDER BY play_count DESC, title ASC LIMIT ? OFFSET ?")) {
                statement.setString(1, pattern);
                statement.setString(2, pattern);
                statement.setString(3, pattern);
                statement.setInt(4, limit);
                statement.setInt(5, offset);
                ok(response, "Search completed", Map.of(
                    "query", q,
                    "songs", songList(statement.executeQuery()),
                    "pagination", AppUtil.paginate(total, limit, page),
                    "search_mode", "basic"
                ));
            }
        }
    }

    private void getSong(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        int id = RequestUtil.intValue(Optional.ofNullable(request.getParameter("id")).orElse("0"));
        if (id <= 0) {
            error(response, 400, "Invalid song ID");
            return;
        }
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT id, title, artist, album, duration, file_path, cover_image, genre, release_year, play_count, created_at, updated_at FROM songs WHERE id = ?")) {
            statement.setInt(1, id);
            try (ResultSet rs = statement.executeQuery()) {
                if (!rs.next()) {
                    error(response, 404, "Song not found");
                    return;
                }
                ok(response, "Song retrieved successfully", Map.of("song", songMap(rs)));
            }
        }
    }

    private void logPlay(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        Map<String, Object> input = json(request);
        int songId = RequestUtil.intValue(input.get("song_id"));
        Integer duration = input.get("duration_played") == null ? null : RequestUtil.intValue(input.get("duration_played"));
        if (songId <= 0) {
            error(response, 400, "Invalid song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            connection.setAutoCommit(false);
            try {
                if (!exists(connection, "SELECT 1 FROM songs WHERE id = ?", songId)) {
                    connection.rollback();
                    error(response, 404, "Song not found");
                    return;
                }
                execute(connection, "UPDATE songs SET play_count = play_count + 1 WHERE id = ?", songId);
                execute(connection, "INSERT INTO song_stats (song_id, play_count) VALUES (?, 1) ON DUPLICATE KEY UPDATE play_count = play_count + 1", songId);
                try (PreparedStatement statement = connection.prepareStatement("INSERT INTO play_history (user_id, song_id, duration_played, played_at) VALUES (?, ?, ?, NOW())")) {
                    statement.setInt(1, user.get().id());
                    statement.setInt(2, songId);
                    if (duration == null) {
                        statement.setNull(3, java.sql.Types.INTEGER);
                    } else {
                        statement.setInt(3, duration);
                    }
                    statement.executeUpdate();
                }
                connection.commit();
                ok(response, "Play logged successfully", Map.of());
            } catch (SQLException ex) {
                connection.rollback();
                throw ex;
            } finally {
                connection.setAutoCommit(true);
            }
        }
    }

    private void listPlaylists(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT p.id, p.name, p.description, p.is_public, p.cover_image, p.created_at, p.updated_at, COUNT(ps.song_id) AS song_count FROM playlists p LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id WHERE p.user_id = ? GROUP BY p.id ORDER BY p.created_at DESC")) {
            statement.setInt(1, user.get().id());
            try (ResultSet rs = statement.executeQuery()) {
                List<Map<String, Object>> playlists = new ArrayList<>();
                while (rs.next()) {
                    Map<String, Object> item = new LinkedHashMap<>();
                    item.put("id", rs.getInt("id"));
                    item.put("name", rs.getString("name"));
                    item.put("description", rs.getString("description"));
                    item.put("is_public", rs.getBoolean("is_public"));
                    item.put("cover_image", rs.getString("cover_image"));
                    item.put("created_at", rs.getTimestamp("created_at"));
                    item.put("updated_at", rs.getTimestamp("updated_at"));
                    item.put("song_count", rs.getInt("song_count"));
                    item.put("created_at_formatted", AppUtil.timeAgo(rs.getTimestamp("created_at")));
                    playlists.add(item);
                }
                ok(response, "Playlists retrieved successfully", Map.of("playlists", playlists));
            }
        }
    }

    private void getPlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        int id = RequestUtil.intValue(Optional.ofNullable(request.getParameter("id")).orElse("0"));
        if (id <= 0) {
            error(response, 400, "Invalid playlist ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            Map<String, Object> playlist;
            try (PreparedStatement statement = connection.prepareStatement("SELECT p.*, COUNT(ps.song_id) AS song_count FROM playlists p LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id WHERE p.id = ? GROUP BY p.id")) {
                statement.setInt(1, id);
                try (ResultSet rs = statement.executeQuery()) {
                    if (!rs.next()) {
                        error(response, 404, "Playlist not found");
                        return;
                    }
                    if (rs.getInt("user_id") != user.get().id() && !rs.getBoolean("is_public")) {
                        error(response, 403, "You do not have permission to view this playlist");
                        return;
                    }
                    playlist = new LinkedHashMap<>();
                    playlist.put("id", rs.getInt("id"));
                    playlist.put("user_id", rs.getInt("user_id"));
                    playlist.put("name", rs.getString("name"));
                    playlist.put("description", rs.getString("description"));
                    playlist.put("song_count", rs.getInt("song_count"));
                    playlist.put("is_public", rs.getBoolean("is_public"));
                }
            }
            try (PreparedStatement statement = connection.prepareStatement("SELECT s.*, ps.position, ps.added_at FROM playlist_songs ps JOIN songs s ON ps.song_id = s.id WHERE ps.playlist_id = ? ORDER BY ps.position ASC")) {
                statement.setInt(1, id);
                ok(response, "Playlist retrieved successfully", Map.of("playlist", playlist, "songs", songList(statement.executeQuery())));
            }
        }
    }

    private void createPlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        Map<String, Object> input = json(request);
        String name = RequestUtil.str(input.get("name"));
        String description = RequestUtil.str(input.get("description"));
        boolean isPublic = RequestUtil.boolValue(input.get("is_public"));
        if (name.isBlank()) {
            error(response, 400, "Playlist name is required");
            return;
        }
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("INSERT INTO playlists (user_id, name, description, is_public, created_at) VALUES (?, ?, ?, ?, NOW())", Statement.RETURN_GENERATED_KEYS)) {
            statement.setInt(1, user.get().id());
            statement.setString(2, name);
            statement.setString(3, description);
            statement.setBoolean(4, isPublic);
            statement.executeUpdate();
            try (ResultSet keys = statement.getGeneratedKeys()) {
                int id = keys.next() ? keys.getInt(1) : 0;
                created(response, "Playlist created successfully", Map.of("playlist_id", id, "name", name));
            }
        }
    }

    private void updatePlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        Map<String, Object> input = json(request);
        int id = RequestUtil.intValue(input.get("id"));
        String name = RequestUtil.str(input.get("name"));
        String description = RequestUtil.str(input.get("description"));
        if (id <= 0 || name.isBlank()) {
            error(response, 400, "Invalid playlist data");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            if (!ownsPlaylist(connection, id, user.get().id())) {
                error(response, 403, "You do not have permission to edit this playlist");
                return;
            }
            execute(connection, "UPDATE playlists SET name = ?, description = ?, updated_at = NOW() WHERE id = ?", name, description, id);
            ok(response, "Playlist updated successfully", Map.of("playlist_id", id, "name", name));
        }
    }

    private void deletePlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        int id = RequestUtil.intValue(Optional.ofNullable(request.getParameter("id")).orElse("0"));
        if (id <= 0) {
            Map<String, Object> input = json(request);
            id = RequestUtil.intValue(input.get("id"));
        }
        if (id <= 0) {
            error(response, 400, "Invalid playlist ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            if (!ownsPlaylist(connection, id, user.get().id())) {
                error(response, 403, "You do not have permission to delete this playlist");
                return;
            }
            execute(connection, "DELETE FROM playlists WHERE id = ?", id);
            ok(response, "Playlist deleted successfully", Map.of());
        }
    }

    private void addSongToPlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        Map<String, Object> input = json(request);
        int playlistId = RequestUtil.intValue(input.get("playlist_id"));
        int songId = RequestUtil.intValue(input.get("song_id"));
        if (playlistId <= 0 || songId <= 0) {
            error(response, 400, "Invalid playlist or song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            if (!ownsPlaylist(connection, playlistId, user.get().id())) {
                error(response, 403, "You do not have permission to modify this playlist");
                return;
            }
            if (!exists(connection, "SELECT 1 FROM songs WHERE id = ?", songId)) {
                error(response, 404, "Song not found");
                return;
            }
            if (exists(connection, "SELECT 1 FROM playlist_songs WHERE playlist_id = ? AND song_id = ?", playlistId, songId)) {
                error(response, 409, "Song already in playlist");
                return;
            }
            int nextPosition = 0;
            try (PreparedStatement statement = connection.prepareStatement("SELECT COALESCE(MAX(position), -1) + 1 AS next_position FROM playlist_songs WHERE playlist_id = ?")) {
                statement.setInt(1, playlistId);
                try (ResultSet rs = statement.executeQuery()) {
                    if (rs.next()) {
                        nextPosition = rs.getInt(1);
                    }
                }
            }
            execute(connection, "INSERT INTO playlist_songs (playlist_id, song_id, position, added_at) VALUES (?, ?, ?, NOW())", playlistId, songId, nextPosition);
            ok(response, "Song added to playlist successfully", Map.of("playlist_id", playlistId, "song_id", songId));
        }
    }

    private void removeSongFromPlaylist(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        int playlistId = RequestUtil.intValue(Optional.ofNullable(request.getParameter("playlist_id")).orElse("0"));
        int songId = RequestUtil.intValue(Optional.ofNullable(request.getParameter("song_id")).orElse("0"));
        if (playlistId <= 0 || songId <= 0) {
            Map<String, Object> input = json(request);
            playlistId = playlistId > 0 ? playlistId : RequestUtil.intValue(input.get("playlist_id"));
            songId = songId > 0 ? songId : RequestUtil.intValue(input.get("song_id"));
        }
        if (playlistId <= 0 || songId <= 0) {
            error(response, 400, "Invalid playlist or song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            if (!ownsPlaylist(connection, playlistId, user.get().id())) {
                error(response, 403, "You do not have permission to modify this playlist");
                return;
            }
            execute(connection, "DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?", playlistId, songId);
            ok(response, "Song removed from playlist successfully", Map.of());
        }
    }

    private void listFavorites(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT s.id, s.title, s.artist, s.album, s.duration, s.file_path, s.cover_image, s.genre, s.play_count, f.added_at FROM favorites f JOIN songs s ON f.song_id = s.id WHERE f.user_id = ? ORDER BY f.added_at DESC")) {
            statement.setInt(1, user.get().id());
            List<Map<String, Object>> favorites = new ArrayList<>();
            try (ResultSet rs = statement.executeQuery()) {
                while (rs.next()) {
                    Map<String, Object> song = songMap(rs);
                    song.put("added_at_formatted", AppUtil.timeAgo(rs.getTimestamp("added_at")));
                    favorites.add(song);
                }
            }
            ok(response, "Favorites retrieved successfully", Map.of("favorites", favorites, "count", favorites.size()));
        }
    }

    private void addFavorite(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        Map<String, Object> input = json(request);
        int songId = RequestUtil.intValue(input.get("song_id"));
        if (songId <= 0) {
            error(response, 400, "Invalid song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            connection.setAutoCommit(false);
            try {
                if (!exists(connection, "SELECT 1 FROM songs WHERE id = ?", songId)) {
                    connection.rollback();
                    error(response, 404, "Song not found");
                    return;
                }
                if (exists(connection, "SELECT 1 FROM favorites WHERE user_id = ? AND song_id = ?", user.get().id(), songId)) {
                    connection.rollback();
                    error(response, 409, "Song already in favorites");
                    return;
                }
                execute(connection, "INSERT INTO favorites (user_id, song_id, added_at) VALUES (?, ?, NOW())", user.get().id(), songId);
                execute(connection, "INSERT INTO song_stats (song_id, likes_count) VALUES (?, 1) ON DUPLICATE KEY UPDATE likes_count = likes_count + 1", songId);
                connection.commit();
                ok(response, "Song added to favorites", Map.of("song_id", songId));
            } catch (SQLException ex) {
                connection.rollback();
                throw ex;
            } finally {
                connection.setAutoCommit(true);
            }
        }
    }

    private void removeFavorite(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        Optional<User> user = requireUser(request, response);
        if (user.isEmpty()) {
            return;
        }
        int songId = RequestUtil.intValue(Optional.ofNullable(request.getParameter("id")).orElse("0"));
        if (songId <= 0) {
            Map<String, Object> input = json(request);
            songId = RequestUtil.intValue(input.get("song_id"));
        }
        if (songId <= 0) {
            error(response, 400, "Invalid song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            connection.setAutoCommit(false);
            try {
                execute(connection, "DELETE FROM favorites WHERE user_id = ? AND song_id = ?", user.get().id(), songId);
                execute(connection, "UPDATE song_stats SET likes_count = GREATEST(0, likes_count - 1) WHERE song_id = ?", songId);
                connection.commit();
                ok(response, "Song removed from favorites", Map.of());
            } catch (SQLException ex) {
                connection.rollback();
                throw ex;
            } finally {
                connection.setAutoCommit(true);
            }
        }
    }

    private void listAlbums(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT album, COUNT(id) AS song_count, (SELECT cover_image FROM songs s2 WHERE s2.album = s1.album ORDER BY id LIMIT 1) AS cover_image FROM songs s1 WHERE album IS NOT NULL AND album != '' GROUP BY album ORDER BY album")) {
            List<Map<String, Object>> albums = new ArrayList<>();
            try (ResultSet rs = statement.executeQuery()) {
                while (rs.next()) {
                    Map<String, Object> album = new LinkedHashMap<>();
                    album.put("album", rs.getString("album"));
                    album.put("song_count", rs.getInt("song_count"));
                    album.put("cover_image", rs.getString("cover_image"));
                    album.put("cover_url", AppUtil.coverUrl(rs.getString("cover_image")));
                    albums.add(album);
                }
            }
            ok(response, "Albums retrieved successfully", Map.of("albums", albums));
        }
    }

    private void listAlbumSongs(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        String album = Optional.ofNullable(request.getParameter("name")).orElse("").trim();
        if (album.isBlank()) {
            error(response, 400, "Album name is required.");
            return;
        }
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT id, title, artist, album, cover_image, file_path, duration, genre, release_year, play_count FROM songs WHERE album = ? ORDER BY title")) {
            statement.setString(1, album);
            ok(response, "Album songs retrieved successfully", Map.of("songs", songList(statement.executeQuery())));
        }
    }

    private void mostPlayed(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        int limit = Math.max(1, RequestUtil.intValue(Optional.ofNullable(request.getParameter("limit")).orElse("10")));
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT s.id, s.title, s.artist, s.cover_image, st.play_count FROM songs s JOIN song_stats st ON s.id = st.song_id ORDER BY st.play_count DESC LIMIT ?")) {
            statement.setInt(1, limit);
            List<Map<String, Object>> songs = new ArrayList<>();
            try (ResultSet rs = statement.executeQuery()) {
                while (rs.next()) {
                    Map<String, Object> song = new LinkedHashMap<>();
                    song.put("id", rs.getInt("id"));
                    song.put("title", rs.getString("title"));
                    song.put("artist", rs.getString("artist"));
                    song.put("cover_image", rs.getString("cover_image"));
                    song.put("cover_url", AppUtil.coverUrl(rs.getString("cover_image")));
                    song.put("play_count", rs.getInt("play_count"));
                    songs.add(song);
                }
            }
            ok(response, "Most played songs retrieved successfully", songs);
        }
    }

    private void mostLiked(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        int limit = Math.max(1, RequestUtil.intValue(Optional.ofNullable(request.getParameter("limit")).orElse("10")));
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("SELECT s.id, s.title, s.artist, s.cover_image, st.likes_count FROM songs s JOIN song_stats st ON s.id = st.song_id ORDER BY st.likes_count DESC LIMIT ?")) {
            statement.setInt(1, limit);
            List<Map<String, Object>> songs = new ArrayList<>();
            try (ResultSet rs = statement.executeQuery()) {
                while (rs.next()) {
                    Map<String, Object> song = new LinkedHashMap<>();
                    song.put("id", rs.getInt("id"));
                    song.put("title", rs.getString("title"));
                    song.put("artist", rs.getString("artist"));
                    song.put("cover_image", rs.getString("cover_image"));
                    song.put("cover_url", AppUtil.coverUrl(rs.getString("cover_image")));
                    song.put("likes_count", rs.getInt("likes_count"));
                    songs.add(song);
                }
            }
            ok(response, "Most liked songs retrieved successfully", songs);
        }
    }

    private void adminStats(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!requireAdmin(request, response)) {
            return;
        }
        Map<String, Object> stats = new LinkedHashMap<>();
        try (Connection connection = Database.getConnection()) {
            stats.put("total_songs", scalar(connection, "SELECT COUNT(*) FROM songs"));
            stats.put("total_users", scalar(connection, "SELECT COUNT(*) FROM users"));
            stats.put("total_plays", scalar(connection, "SELECT COUNT(*) FROM play_history"));
            stats.put("total_favorites", scalar(connection, "SELECT COUNT(*) FROM favorites"));
            stats.put("total_playlists", scalar(connection, "SELECT COUNT(*) FROM playlists"));
            stats.put("recent_users", scalar(connection, "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"));
        }
        ok(response, "Admin statistics retrieved successfully", stats);
    }

    private void uploadSong(HttpServletRequest request, HttpServletResponse response) throws IOException, ServletException, SQLException {
        if (!requireAdmin(request, response)) {
            return;
        }
        String title = request.getParameter("title");
        String artist = request.getParameter("artist");
        String album = Optional.ofNullable(request.getParameter("album")).orElse("");
        String genre = Optional.ofNullable(request.getParameter("genre")).orElse("");
        int releaseYear = RequestUtil.intValue(Optional.ofNullable(request.getParameter("release_year")).orElse("0"));
        Part audioFile = request.getPart("audio_file");
        Part coverImage = request.getPart("cover_image");
        if (title == null || title.isBlank() || artist == null || artist.isBlank()) {
            error(response, 400, "Title and artist are required");
            return;
        }
        if (audioFile == null || coverImage == null || audioFile.getSize() == 0 || coverImage.getSize() == 0) {
            error(response, 400, "Audio file and cover image are required");
            return;
        }
        Path uploadBase = Path.of(AppConfig.get("app.uploadBase", "/data/uploads"));
        Path audioDir = uploadBase.resolve("songs");
        Path coverDir = uploadBase.resolve("covers");
        Files.createDirectories(audioDir);
        Files.createDirectories(coverDir);
        String audioName = storedFilename(audioFile);
        String coverName = storedFilename(coverImage);
        Path audioPath = audioDir.resolve(audioName);
        Path coverPath = coverDir.resolve(coverName);
        Files.copy(audioFile.getInputStream(), audioPath, StandardCopyOption.REPLACE_EXISTING);
        Files.copy(coverImage.getInputStream(), coverPath, StandardCopyOption.REPLACE_EXISTING);
        try (Connection connection = Database.getConnection();
             PreparedStatement statement = connection.prepareStatement("INSERT INTO songs (title, artist, album, duration, file_path, cover_image, genre, release_year, created_at) VALUES (?, ?, ?, 0, ?, ?, ?, ?, NOW())", Statement.RETURN_GENERATED_KEYS)) {
            statement.setString(1, title);
            statement.setString(2, artist);
            statement.setString(3, album);
            statement.setString(4, audioName);
            statement.setString(5, coverName);
            statement.setString(6, genre);
            if (releaseYear > 0) {
                statement.setInt(7, releaseYear);
            } else {
                statement.setNull(7, java.sql.Types.INTEGER);
            }
            statement.executeUpdate();
            try (ResultSet keys = statement.getGeneratedKeys()) {
                int id = keys.next() ? keys.getInt(1) : 0;
                created(response, "Song uploaded successfully", Map.of("song_id", id, "title", title, "artist", artist, "audio_file", audioName, "cover_image", coverName));
            }
        } catch (SQLException ex) {
            Files.deleteIfExists(audioPath);
            Files.deleteIfExists(coverPath);
            throw ex;
        }
    }

    private void updateSong(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!requireAdmin(request, response)) {
            return;
        }
        Map<String, Object> input = json(request);
        int id = RequestUtil.intValue(input.get("id"));
        String title = RequestUtil.str(input.get("title"));
        String artist = RequestUtil.str(input.get("artist"));
        String album = RequestUtil.str(input.get("album"));
        String genre = RequestUtil.str(input.get("genre"));
        int releaseYear = RequestUtil.intValue(input.get("release_year"));
        if (id <= 0 || title.isBlank() || artist.isBlank()) {
            error(response, 400, "Invalid song data");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            execute(connection, "UPDATE songs SET title = ?, artist = ?, album = ?, genre = ?, release_year = ?, updated_at = NOW() WHERE id = ?", title, artist, album, genre, releaseYear == 0 ? null : releaseYear, id);
            ok(response, "Song updated successfully", Map.of("id", id));
        }
    }

    private void deleteSong(HttpServletRequest request, HttpServletResponse response) throws IOException, SQLException {
        if (!requireAdmin(request, response)) {
            return;
        }
        Map<String, Object> input = json(request);
        int id = RequestUtil.intValue(input.get("id"));
        if (id <= 0) {
            id = RequestUtil.intValue(Optional.ofNullable(request.getParameter("id")).orElse("0"));
        }
        if (id <= 0) {
            error(response, 400, "Invalid song ID");
            return;
        }
        try (Connection connection = Database.getConnection()) {
            String filePath = null;
            String coverImage = null;
            try (PreparedStatement statement = connection.prepareStatement("SELECT file_path, cover_image FROM songs WHERE id = ?")) {
                statement.setInt(1, id);
                try (ResultSet rs = statement.executeQuery()) {
                    if (!rs.next()) {
                        error(response, 404, "Song not found");
                        return;
                    }
                    filePath = rs.getString("file_path");
                    coverImage = rs.getString("cover_image");
                }
            }
            execute(connection, "DELETE FROM songs WHERE id = ?", id);
            if (coverImage != null && coverImage.contains("_")) {
                Path uploadBase = Path.of(AppConfig.get("app.uploadBase", "/data/uploads"));
                Files.deleteIfExists(uploadBase.resolve("songs").resolve(filePath));
                Files.deleteIfExists(uploadBase.resolve("covers").resolve(coverImage));
            }
            ok(response, "Song deleted successfully", Map.of());
        }
    }

    private int countSongs(Connection connection, String genre, String pattern) throws SQLException {
        String sql = "SELECT COUNT(*) FROM songs";
        if (genre != null && !genre.isBlank()) {
            sql += " WHERE genre = ?";
        } else if (pattern != null) {
            sql += " WHERE title LIKE ? OR artist LIKE ? OR album LIKE ?";
        }
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            if (genre != null && !genre.isBlank()) {
                statement.setString(1, genre);
            } else if (pattern != null) {
                statement.setString(1, pattern);
                statement.setString(2, pattern);
                statement.setString(3, pattern);
            }
            try (ResultSet rs = statement.executeQuery()) {
                return rs.next() ? rs.getInt(1) : 0;
            }
        }
    }

    private List<Map<String, Object>> songList(ResultSet rs) throws SQLException {
        List<Map<String, Object>> songs = new ArrayList<>();
        while (rs.next()) {
            songs.add(songMap(rs));
        }
        return songs;
    }

    private Map<String, Object> songMap(ResultSet rs) throws SQLException {
        Map<String, Object> song = new LinkedHashMap<>();
        song.put("id", rs.getInt("id"));
        song.put("title", rs.getString("title"));
        song.put("artist", rs.getString("artist"));
        song.put("album", rs.getString("album"));
        int duration = rs.getInt("duration");
        boolean durationWasNull = rs.wasNull();
        song.put("duration", durationWasNull ? null : duration);
        song.put("duration_formatted", AppUtil.formatDuration(durationWasNull ? null : duration));
        song.put("file_path", rs.getString("file_path"));
        song.put("cover_image", rs.getString("cover_image"));
        song.put("cover_url", AppUtil.coverUrl(rs.getString("cover_image")));
        song.put("audio_url", AppUtil.audioUrl(rs.getString("file_path")));
        song.put("genre", rs.getString("genre"));
        song.put("release_year", optionalColumn(rs, "release_year"));
        song.put("play_count", rs.getInt("play_count"));
        try {
            Timestamp createdAt = rs.getTimestamp("created_at");
            if (createdAt != null) {
                song.put("created_at", createdAt);
            }
        } catch (SQLException ignored) {
        }
        return song;
    }

    private Object optionalColumn(ResultSet rs, String columnName) {
        try {
            return rs.getObject(columnName);
        } catch (SQLException ignored) {
            return null;
        }
    }

    private boolean ownsPlaylist(Connection connection, int playlistId, int userId) throws SQLException {
        return exists(connection, "SELECT 1 FROM playlists WHERE id = ? AND user_id = ?", playlistId, userId);
    }

    private boolean exists(Connection connection, String sql, Object... params) throws SQLException {
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            bind(statement, params);
            try (ResultSet rs = statement.executeQuery()) {
                return rs.next();
            }
        }
    }

    private void execute(Connection connection, String sql, Object... params) throws SQLException {
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            bind(statement, params);
            statement.executeUpdate();
        }
    }

    private void bind(PreparedStatement statement, Object... params) throws SQLException {
        for (int i = 0; i < params.length; i++) {
            if (params[i] == null) {
                statement.setObject(i + 1, null);
            } else {
                statement.setObject(i + 1, params[i]);
            }
        }
    }

    private int scalar(Connection connection, String sql) throws SQLException {
        try (PreparedStatement statement = connection.prepareStatement(sql); ResultSet rs = statement.executeQuery()) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private String storedFilename(Part part) {
        String original = Path.of(part.getSubmittedFileName()).getFileName().toString();
        String extension = original.contains(".") ? original.substring(original.lastIndexOf('.')) : "";
        return UUID.randomUUID().toString().replace("-", "") + "_" + System.currentTimeMillis() + extension;
    }
}
