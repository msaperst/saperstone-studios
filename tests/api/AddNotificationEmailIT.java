import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.net.UnknownHostException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class AddNotificationEmailIT extends SelenifiedBase {

    private int albumId = 9999;

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('" + albumId + "', 'sample-album', 'sample album for testing', '');");
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumId);
        SQL.execute("DELETE FROM `notification_emails` WHERE `notification_emails`.`album` = " + albumId);
    }

    @Test(groups = {"api", "add-notification-email"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/add-notification-email.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().message("Album id is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email"})
    public void blankAlbumId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", "");
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Album id cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email"})
    public void badAlbumId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", "999999999");
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("That ID doesn't match any albums");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email", "needs-album"})
    public void noEmail() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", albumId);
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Email is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email", "needs-album"})
    public void blankEmail() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", albumId);
        data.put("email", "");
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Email cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email", "needs-album"})
    public void goodEmailUser() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", albumId);
        data.put("email", "max@max.max");
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("");
        //TODO needs to login to use that user instead
        ResultSet rs = SQL.select("SELECT * FROM `notification_emails` WHERE `album` = " + albumId);
        while (rs.next()) {
            checkDbEquals("max@max.max", rs, "email");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "user");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "add-notification-email", "needs-album"})
    public void goodEmailNoUser() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("album", albumId);
        data.put("email", "max@max.max");
        Response response = call.post("api/add-notification-email.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("");
        ResultSet rs = SQL.select("SELECT * FROM `notification_emails` WHERE `album` = " + albumId);
        while (rs.next()) {
            checkDbEquals("max@max.max", rs, "email");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "user");
        }
        // verify no issues
        finish();
    }
}
