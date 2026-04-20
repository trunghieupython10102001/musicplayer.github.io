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
            if (("/".equals(path) || "/index".equals(path) || "/dashboard".equals(path) || "/admin".equals(path)) && currentUser.isEmpty()) {
                response.sendRedirect("/login?redirect=" + path);
                return;
            }
            if (("/login".equals(path) || "/register".equals(path) || "/forgot-password".equals(path) || "/reset-password".equals(path)) && currentUser.isPresent()) {
                response.sendRedirect("/index");
                return;
            }
            if ("/reset-password".equals(path)) {
                String token = request.getParameter("token");
                request.setAttribute("token", token == null ? "" : token);
            }
            if ("/admin".equals(path) && (currentUser.isEmpty() || !"admin".equals(currentUser.get().role()))) {
                response.sendRedirect("/index");
                return;
            }
            String view = switch (path) {
                case "/", "/index" -> "/WEB-INF/views/index.jsp";
                case "/login" -> "/WEB-INF/views/login.jsp";
                case "/register" -> "/WEB-INF/views/register.jsp";
                case "/dashboard" -> "/WEB-INF/views/dashboard.jsp";
                case "/admin" -> "/WEB-INF/views/admin.jsp";
                case "/forgot-password" -> "/WEB-INF/views/forgot-password.jsp";
                case "/reset-password" -> "/WEB-INF/views/reset-password.jsp";
                default -> "/WEB-INF/views/index.jsp";
            };
            forward(request, response, view);
        } catch (SQLException ex) {
            throw new ServletException(ex);
        }
    }
}
