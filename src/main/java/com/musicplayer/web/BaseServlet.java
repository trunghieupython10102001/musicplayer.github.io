package com.musicplayer.web;

import com.musicplayer.util.JsonUtil;
import com.musicplayer.util.RequestUtil;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.Map;

public abstract class BaseServlet extends HttpServlet {
    protected Map<String, Object> json(HttpServletRequest request) throws IOException {
        return RequestUtil.readJson(request);
    }

    protected void ok(HttpServletResponse response, String message, Object data) throws IOException {
        JsonUtil.write(response, 200, true, message, data);
    }

    protected void created(HttpServletResponse response, String message, Object data) throws IOException {
        JsonUtil.write(response, 201, true, message, data);
    }

    protected void error(HttpServletResponse response, int status, String message) throws IOException {
        JsonUtil.write(response, status, false, message, Map.of());
    }

    protected void forward(HttpServletRequest request, HttpServletResponse response, String jsp) throws ServletException, IOException {
        request.getRequestDispatcher(jsp).forward(request, response);
    }
}
