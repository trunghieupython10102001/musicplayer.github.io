package com.musicplayer.util;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.LinkedHashMap;
import java.util.Map;

public final class JsonUtil {
    public static final Gson GSON = new GsonBuilder().disableHtmlEscaping().create();

    private JsonUtil() {
    }

    public static Map<String, Object> response(boolean success, String message, Object data) {
        Map<String, Object> body = new LinkedHashMap<>();
        body.put("success", success);
        body.put("message", message);
        body.put("data", data == null ? Map.of() : data);
        return body;
    }

    public static void write(HttpServletResponse response, int status, boolean success, String message, Object data) throws IOException {
        response.setStatus(status);
        response.setContentType("application/json; charset=UTF-8");
        response.getWriter().write(GSON.toJson(response(success, message, data)));
    }
}
