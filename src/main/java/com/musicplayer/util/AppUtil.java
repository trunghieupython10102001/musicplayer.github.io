package com.musicplayer.util;

import com.musicplayer.config.AppConfig;

import java.time.Duration;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.HashMap;
import java.util.Map;

public final class AppUtil {
    private AppUtil() {
    }

    public static String assetBaseUrl() {
        return AppConfig.get("app.baseUrl", "http://localhost:8080");
    }

    public static String coverUrl(String filename) {
        if (filename == null || filename.isBlank()) {
            return assetBaseUrl() + "/assets/img/default.png";
        }
        if (filename.contains("_")) {
            return assetBaseUrl() + "/uploads/covers/" + filename;
        }
        return assetBaseUrl() + "/assets/img/" + filename;
    }

    public static String audioUrl(String filename) {
        if (filename == null || filename.isBlank()) {
            return "";
        }
        if (filename.contains("_")) {
            return assetBaseUrl() + "/uploads/songs/" + filename;
        }
        return assetBaseUrl() + "/assets/audio/" + filename;
    }

    public static String formatDuration(Integer seconds) {
        if (seconds == null || seconds <= 0) {
            return "0:00";
        }
        return (seconds / 60) + ":" + String.format("%02d", seconds % 60);
    }

    public static Map<String, Object> paginate(int totalItems, int itemsPerPage, int currentPage) {
        int totalPages = Math.max(1, (int) Math.ceil((double) totalItems / Math.max(itemsPerPage, 1)));
        int page = Math.max(1, Math.min(currentPage, totalPages));
        Map<String, Object> data = new HashMap<>();
        data.put("total_items", totalItems);
        data.put("items_per_page", itemsPerPage);
        data.put("total_pages", totalPages);
        data.put("current_page", page);
        data.put("offset", (page - 1) * itemsPerPage);
        data.put("has_previous", page > 1);
        data.put("has_next", page < totalPages);
        return data;
    }

    public static String timeAgo(java.sql.Timestamp timestamp) {
        if (timestamp == null) {
            return "just now";
        }
        LocalDateTime dateTime = timestamp.toLocalDateTime();
        LocalDateTime now = LocalDateTime.now(ZoneId.of("Asia/Ho_Chi_Minh"));
        long seconds = Duration.between(dateTime, now).getSeconds();
        if (seconds < 60) {
            return "just now";
        }
        long minutes = seconds / 60;
        if (minutes < 60) {
            return minutes + " minute" + (minutes > 1 ? "s" : "") + " ago";
        }
        long hours = minutes / 60;
        if (hours < 24) {
            return hours + " hour" + (hours > 1 ? "s" : "") + " ago";
        }
        long days = hours / 24;
        if (days < 7) {
            return days + " day" + (days > 1 ? "s" : "") + " ago";
        }
        return dateTime.truncatedTo(ChronoUnit.DAYS).format(DateTimeFormatter.ofPattern("MMM d, yyyy"));
    }
}
