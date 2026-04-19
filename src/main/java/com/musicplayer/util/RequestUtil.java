package com.musicplayer.util;

import com.google.gson.reflect.TypeToken;
import jakarta.servlet.http.HttpServletRequest;

import java.io.BufferedReader;
import java.io.IOException;
import java.lang.reflect.Type;
import java.util.HashMap;
import java.util.Map;

public final class RequestUtil {
    private static final Type MAP_TYPE = new TypeToken<Map<String, Object>>() {}.getType();

    private RequestUtil() {
    }

    public static Map<String, Object> readJson(HttpServletRequest request) throws IOException {
        StringBuilder builder = new StringBuilder();
        try (BufferedReader reader = request.getReader()) {
            String line;
            while ((line = reader.readLine()) != null) {
                builder.append(line);
            }
        }
        if (builder.isEmpty()) {
            return new HashMap<>();
        }
        Map<String, Object> data = JsonUtil.GSON.fromJson(builder.toString(), MAP_TYPE);
        return data == null ? new HashMap<>() : data;
    }

    public static int intValue(Object value) {
        if (value instanceof Number number) {
            return number.intValue();
        }
        try {
            return Integer.parseInt(String.valueOf(value));
        } catch (Exception ex) {
            return 0;
        }
    }

    public static boolean boolValue(Object value) {
        if (value instanceof Boolean bool) {
            return bool;
        }
        return Boolean.parseBoolean(String.valueOf(value));
    }

    public static String str(Object value) {
        return value == null ? "" : String.valueOf(value).trim();
    }
}
