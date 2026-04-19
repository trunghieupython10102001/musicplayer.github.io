package com.musicplayer.service;

import com.musicplayer.config.AppConfig;
import jakarta.mail.Message;
import jakarta.mail.MessagingException;
import jakarta.mail.Session;
import jakarta.mail.Transport;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;

import java.io.UnsupportedEncodingException;
import java.util.Properties;

public final class MailService {
    private MailService() {
    }

    public static void sendPasswordResetEmail(String toEmail, String userName, String resetLink) throws MessagingException {
        Session session = Session.getInstance(mailProperties());

        MimeMessage message = new MimeMessage(session);
        try {
            message.setFrom(new InternetAddress(
                AppConfig.get("mail.fromEmail", "noreply@musicplayer.local"),
                AppConfig.get("mail.fromName", "Music Player")
            ));
        } catch (UnsupportedEncodingException ex) {
            throw new MessagingException("Failed to encode sender name", ex);
        }
        message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(toEmail, false));
        message.setSubject("Reset Your Music Player Password", "UTF-8");
        message.setContent(passwordResetHtml(userName, resetLink), "text/html; charset=UTF-8");

        Transport.send(message);
    }

    private static Properties mailProperties() {
        Properties properties = new Properties();
        properties.put("mail.smtp.host", AppConfig.get("MAIL_HOST", AppConfig.get("mail.host", "mailhog")));
        properties.put("mail.smtp.port", AppConfig.get("MAIL_PORT", AppConfig.get("mail.port", "1025")));
        properties.put("mail.smtp.auth", AppConfig.get("MAIL_AUTH", AppConfig.get("mail.auth", "false")));
        properties.put("mail.smtp.starttls.enable", AppConfig.get("MAIL_STARTTLS", AppConfig.get("mail.starttls", "false")));
        return properties;
    }

    private static String passwordResetHtml(String userName, String resetLink) {
        return "<!DOCTYPE html>"
            + "<html><head><meta charset=\"UTF-8\"><style>"
            + "body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}"
            + ".container{max-width:600px;margin:0 auto;padding:20px;}"
            + ".header{background-color:#1DB954;color:white;padding:20px;text-align:center;border-radius:5px 5px 0 0;}"
            + ".content{background-color:#f9f9f9;padding:20px;border:1px solid #ddd;}"
            + ".footer{background-color:#f0f0f0;padding:15px;text-align:center;font-size:12px;color:#666;border-radius:0 0 5px 5px;}"
            + ".button{display:inline-block;background-color:#1DB954;color:white;padding:12px 30px;text-decoration:none;border-radius:5px;margin:20px 0;}"
            + ".warning{background-color:#fff3cd;border:1px solid #ffc107;padding:10px;border-radius:3px;margin:15px 0;}"
            + "</style></head><body>"
            + "<div class=\"container\">"
            + "<div class=\"header\"><h1>Music Player</h1></div>"
            + "<div class=\"content\">"
            + "<p>Hi " + escape(userName) + ",</p>"
            + "<p>We received a request to reset your password. Click the button below to create a new password:</p>"
            + "<p><a href=\"" + escape(resetLink) + "\" class=\"button\">Reset Password</a></p>"
            + "<p>Or copy and paste this link in your browser:</p>"
            + "<p style=\"word-break:break-all;background-color:#f0f0f0;padding:10px;border-radius:3px;\">" + escape(resetLink) + "</p>"
            + "<div class=\"warning\"><strong>Security Notice:</strong> This link will expire in 1 hour. If you didn't request a password reset, please ignore this email.</div>"
            + "<p>Best regards,<br>The Music Player Team</p>"
            + "</div>"
            + "<div class=\"footer\"><p>This is an automated email. Please do not reply to this message.</p></div>"
            + "</div></body></html>";
    }

    private static String escape(String input) {
        return input == null ? "" : input.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;").replace("\"", "&quot;");
    }
}
