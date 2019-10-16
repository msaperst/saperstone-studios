import org.testng.log4testng.Logger;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.Statement;

public class SQL {
    private static final Logger log = Logger.getLogger(SQL.class);

    public static ResultSet select(String query) {
        try {
            String url = "jdbc:mysql://localhost:3406/saperstone-studios";
            Class.forName("com.mysql.jdbc.Driver");
            Connection conn = DriverManager.getConnection(url, "saperstone-studios", "secret");
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(query);
//            conn.close();
            return rs;
        } catch (Exception e) {
            log.error(e);
            return null;
        }
    }

    public static boolean execute(String query) {
        try {
            String url = "jdbc:mysql://localhost:3406/saperstone-studios";
            Class.forName("com.mysql.jdbc.Driver");
            Connection conn = DriverManager.getConnection(url, "saperstone-studios", "secret");
            Statement stmt = conn.createStatement();
            boolean success = stmt.execute(query);
            conn.close();
            return success;
        } catch (Exception e) {
            log.error(e);
            return false;
        }
    }
}