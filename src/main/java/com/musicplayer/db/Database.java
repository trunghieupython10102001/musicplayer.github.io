package com.musicplayer.db;

import com.musicplayer.config.AppConfig;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public final class Database {
    static {
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (ClassNotFoundException ex) {
            throw new ExceptionInInitializerError(ex);
        }
    }

    private Database() {
    }

    public static Connection getConnection() throws SQLException {
        String host = AppConfig.get("DB_HOST", "mysql");
        String name = AppConfig.get("DB_NAME", "musicplayer");
        String user = AppConfig.get("DB_USER", "musicplayer_user");
        String pass = AppConfig.get("DB_PASS", "musicplayer_pass");
        String url = "jdbc:mysql://" + host + ":3306/" + name + "?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=Asia/Ho_Chi_Minh&characterEncoding=utf8";
        return DriverManager.getConnection(url, user, pass);
    }
}
