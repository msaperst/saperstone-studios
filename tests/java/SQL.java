import org.testng.log4testng.Logger;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.sql.*;
import java.util.Properties;

public class SQL {
    private static final Logger log = Logger.getLogger(SQL.class);

    public static ResultSet select(String query) {
        try {
            Connection conn = getConnection();
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(query);
            return rs;
        } catch (Exception e) {
            log.error(e);
            return null;
        }
    }

    public static boolean execute(String query) {
        try {
            Connection conn = getConnection();
            Statement stmt = conn.createStatement();
            boolean success = stmt.execute(query);
            conn.close();
            return success;
        } catch (Exception e) {
            log.error(e);
            return false;
        }
    }

    private static Connection getConnection() throws SQLException, ClassNotFoundException, IOException {
        String url = "jdbc:mysql://localhost:" + getProperty("DB_PORT") + "/" + getProperty("DB_NAME");
        Class.forName("com.mysql.jdbc.Driver");
        return DriverManager.getConnection(url, getProperty("DB_USER"), getProperty("DB_PASS"));
    }

    private static String getProperty(String property) throws IOException {
        File f = new File(".env");
        Properties pro = new Properties();
        FileInputStream in = new FileInputStream(f);
        pro.load(in);
        return pro.getProperty(property);
    }
}