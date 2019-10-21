import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.text.SimpleDateFormat;
import java.util.HashMap;
import java.util.Map;

public class GetAlbumIT extends BaseBrowserless {

    private int albumId = 9999;
    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.0");

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('" + albumId + "', 'sample-album', 'sample album for testing', '');");
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumId);
    }

    @Test(groups = {"api", "get-album"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.get("api/get-album.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("Album id is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album"})
    public void blankPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("id", "");
        Response response = call.get("api/get-album.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        response.azzert().equals("Album id cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album"})
    public void badPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("id", "999999999");
        Response response = call.get("api/get-album.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        response.azzert().equals("That ID doesn't match any albums");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album", "needs-album"})
    public void noMessage() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("id", albumId);
        Response response = call.get("api/get-album.php", new Request().setUrlParams(data));
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
