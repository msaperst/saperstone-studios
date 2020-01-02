import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class FindAlbumIT extends SelenifiedBase {

    private int albumId = 9999;

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `code`) VALUES ('" + albumId + "', 'sample-album', 'sample album for testing', '', 'sample-album');");
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumId);
    }

    @Test(groups = {"api", "find-album"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.get("api/find-album.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().message("Album code is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "find-album"})
    public void emptyCodeTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("code", "");
        Response response = call.get("api/find-album.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Album code cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "find-album"})
    public void badCodeTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("code", "some-bogus-album");
        Response response = call.get("api/find-album.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        response.assertEquals().message("That code doesn't match any albums");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "find-album", "needs-album"})
    public void goodCodeTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("code", "sample-album");
        Response response = call.get("api/find-album.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        response.assertEquals().message(String.valueOf(albumId));
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out (Adding album to user's list)
}
