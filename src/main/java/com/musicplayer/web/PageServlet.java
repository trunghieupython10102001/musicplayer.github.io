package com.musicplayer.web;

import com.musicplayer.model.User;
import com.musicplayer.service.AuthService;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.SQLException;
import java.util.Optional;

public class PageServlet extends BaseServlet {
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        try {
            Optional<User> currentUser = AuthService.currentUser(request);
            request.setAttribute("currentUser", currentUser.orElse(null));
            if (("/".equals(path) || "/index.php".equals(path) || "/dashboard.php".equals(path) || "/admin.php".equals(path)) && currentUser.isEmpty()) {
                response.sendRedirect("/login.php?redirect=" + path);
                return;
            }
            if (("/login.php".equals(path) || "/register.php".equals(path) || "/forgot-password.php".equals(path) || "/reset-password.php".equals(path)) && currentUser.isPresent()) {
                response.sendRedirect("/index.php");
                return;
            }
            if ("/reset-password.php".equals(path)) {
                String token = request.getParameter("token");
                request.setAttribute("token", token == null ? "" : token);
            }
            if ("/admin.php".equals(path) && (currentUser.isEmpty() || !"admin".equals(currentUser.get().role()))) {
                response.sendRedirect("/index.php");
                return;
            }
            String view = switch (path) {
                case "/", "/index.php" -> "/WEB-INF/views/index.jsp";
                case "/login.php" -> "/WEB-INF/views/login.jsp";
                case "/register.php" -> "/WEB-INF/views/register.jsp";
                case "/dashboard.php" -> "/WEB-INF/views/dashboard.jsp";
                case "/admin.php" -> "/WEB-INF/views/admin.jsp";
                case "/forgot-password.php" -> "/WEB-INF/views/forgot-password.jsp";
                case "/reset-password.php" -> "/WEB-INF/views/reset-password.jsp";
                default -> "/WEB-INF/views/index.jsp";
            };
            forward(request, response, view);
        } catch (SQLException ex) {
            throw new ServletException(ex);
        }
    }
}
