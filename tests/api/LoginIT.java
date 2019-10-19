import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class LoginIT extends BaseBrowser {

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() {
        SQL.execute("INSERT INTO users ( id, usr, pass, firstName, lastName, email, role, active, hash ) VALUES (9998, 'active-test-user', 'password', 'test', 'user', 'active-test-user@max.max', 'downloader', '1', '123456789WERTYUDFGHJK' );");
        SQL.execute("INSERT INTO users ( id, usr, pass, firstName, lastName, email, role, active, hash ) VALUES (9999, 'inactive-test-user', 'password', 'test', 'user', 'inactive-test-user@max.max', 'downloader', '0', '56789ERTYUIFGHJKL' );");
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `users` WHERE `users`.`id` = 9998");
        SQL.execute("DELETE FROM `users` WHERE `users`.`id` = 9999");
        SQL.execute("DELETE FROM `user_logs` WHERE `user_logs`.`user` = 9999");
        SQL.execute("DELETE FROM `user_logs` WHERE `user_logs`.`user` = 9999");
    }

    @Test(groups = {"api", "login"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/login.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void logoutTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Logout");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void noUserTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Login");
        data.put("password", "max");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("All the fields must be filled in!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void noPassTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Login");
        data.put("username", "max");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("All the fields must be filled in!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void invalidLoginTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Login");
        data.put("username", "max");
        data.put("password", "password");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Credentials do not match our records!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void inactiveLoginTest() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Login");
        data.put("username", "inactive-test-user");
        data.put("password", "password");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Sorry, you account has been deactivated. Please                     <a target=\"_blank\" href=\"mailto:webmaster@saperstonestudios.com\">contact our                    webmaster</a> to get this resolved.");
        ResultSet rs = SQL.select("SELECT * FROM `users` WHERE `id` = 9999");
        while (rs.next()) {
            String lastLogin = rs.getString("lastLogin");
            if (lastLogin == null) {
                this.apps.get().getReporter().pass("", "DB Results contain lastLogin of null", "DB Results contain lastLogin of '" + lastLogin + "'");
            } else {
                this.apps.get().getReporter().fail("", "DB Results contain lastLogin of null", "DB Results contain lastLogin of '" + lastLogin + "'");
            }
        }
        rs = SQL.select("SELECT * FROM `user_logs` WHERE `user` = 9998");
        if (rs.getFetchSize() == 0) {
            this.apps.get().getReporter().pass("", "DB Results contain no logs of user", "DB Results contains '" + rs.getFetchSize() + "' instances of user logs");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain no logs of user", "DB Results contains '" + rs.getFetchSize() + "' instances of user logs");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "login"})
    public void activeLoginTest() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("submit", "Login");
        data.put("username", "active-test-user");
        data.put("password", "password");
        Response response = call.post("api/login.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("");
        ResultSet rs = SQL.select("SELECT * FROM `users` WHERE `id` = 9998");
        while (rs.next()) {
            String lastLogin = rs.getString("lastLogin");
            if (lastLogin != null) {
                this.apps.get().getReporter().pass("", "DB Results contain lastLogin of not null", "DB Results contain lastLogin of '" + lastLogin + "'");
            } else {
                this.apps.get().getReporter().fail("", "DB Results contain lastLogin of not null", "DB Results contain lastLogin of '" + lastLogin + "'");
            }
        }
        rs = SQL.select("SELECT * FROM `user_logs` WHERE `user` = 9998");
        while (rs.next()) {
            String action = rs.getString("action");
            if ("Logged In".equals(action)) {
                this.apps.get().getReporter().pass("", "DB Results contains records of logging in", "DB Results contains record of '" + action + "'");
            } else {
                this.apps.get().getReporter().fail("", "DB Results contains records of logging in", "DB Results contains record of '" + action + "'");
            }
        }
        //TODO check session details
        //TODO check cookie details
        // verify no issues
        finish();
    }
}
