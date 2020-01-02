import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.google.gson.JsonObject;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class GetAlbumImagesIT extends SelenifiedBase {

    private int albumId = 9999;
    private String[] files = {"file.1.png", "file.2.png", "file.3.png", "file.4.png", "file.5.png",};

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `code`) VALUES ('" + albumId + "', 'sample-album', 'sample album for testing', '', '1234');");
        for (String file : files) {
            SQL.execute("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) " +
                    "VALUES (NULL, '" + albumId + "', '" + file + "', '" + file.split("\\.")[1] + "', '/albums/" + file + "', '600', '400', '1');");
        }
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumId);
        SQL.execute("DELETE FROM `album_images` WHERE `album_images`.`album` = " + albumId);

    }

    @Test(groups = {"api", "get-album-images"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        JsonObject json = new JsonObject();
        json.addProperty("err", "Album id is required!");
        Response response = call.get("api/get-album-images.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().objectData(json);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images"})
    public void blankPostId() {
        Call call = this.calls.get();
        JsonObject json = new JsonObject();
        json.addProperty("err", "Album id cannot be blank!");
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", "");
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        response.assertEquals().objectData(json);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images"})
    public void badPostId() {
        Call call = this.calls.get();
        JsonObject json = new JsonObject();
        json.addProperty("err", "Album doesn't exist!");
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", "999999999");
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        response.assertEquals().objectData(json);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out (need tests surrounding logged in, without code, to ensure access is restricted)

    @Test(groups = {"api", "get-album-images", "needs-album"})
    public void allImages() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", albumId);
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        assertArraySize(response.getArrayData(), 5);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images", "needs-album"})
    public void startAt3() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", albumId);
        data.put("start", 2);
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        assertArraySize(response.getArrayData(), 3);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images", "needs-album"})
    public void startAt3Only2() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", albumId);
        data.put("start", 2);
        data.put("howMany", 2);
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        assertArraySize(response.getArrayData(), 2);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images", "needs-album"})
    public void startAt3TooMany() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", albumId);
        data.put("start", 2);
        data.put("howMany", 5);
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        assertArraySize(response.getArrayData(), 3);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-album-images", "needs-album"})
    public void startAtTooMany() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("albumId", albumId);
        data.put("start", 7);
        Response response = call.get("api/get-album-images.php", new Request().setUrlParams(data));
        response.assertEquals().code(200);
        assertArraySize(response.getArrayData(), 0);
        // verify no issues
        finish();
    }


}
