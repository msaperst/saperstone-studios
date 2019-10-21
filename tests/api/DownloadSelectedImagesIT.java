import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.coveros.selenified.utilities.Property;
import org.apache.commons.io.FileUtils;
import org.testng.ITestContext;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.io.File;
import java.io.IOException;
import java.net.URL;
import java.net.UnknownHostException;
import java.text.SimpleDateFormat;
import java.util.*;

public class DownloadSelectedImagesIT extends BaseBrowser {

    private SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd HH-mm-ss");
    private int albumIdAll = 9996;
    private int albumIdSome = 9997;
    private int badAlbumId = 9999;
    private String[] files = {"file.1.png", "file.2.png", "file.3.png", "file.4.png", "file.5.png",};

    @BeforeMethod(groups = {"needs-album"})
    public void createAlbum() throws IOException {
        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('" + albumIdAll + "', 'sample-album-download-all', 'sample album for testing', '');");
        SQL.execute("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '" + albumIdAll + "', '*');");

        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('" + albumIdSome + "', 'sample-album-download-some', 'sample album for testing', '');");
        SQL.execute("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '" + albumIdSome + "', '2');");
        SQL.execute("INSERT INTO `download_rights` (`user`, `album`, `image`) VALUES ('0', '" + albumIdSome + "', '3');");

        SQL.execute("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('" + badAlbumId + "', 'sample-album-no-access', 'sample album for testing without any download access', '');");

        for (String file : files) {
            SQL.execute("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) " +
                    "VALUES (NULL, '" + albumIdAll + "', '" + file + "', '" + file.split("\\.")[1] + "', '/albums/" + file + "', '600', '400', '1');");
            SQL.execute("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) " +
                    "VALUES (NULL, '" + albumIdSome + "', '" + file + "', '" + file.split("\\.")[1] + "', '/albums/" + file + "', '600', '400', '1');");
            File imageFile = new File("content/albums/" + file);
            imageFile.createNewFile();
        }
    }

    @AfterMethod(groups = {"needs-album"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumIdAll);
        SQL.execute("DELETE FROM `download_rights` WHERE `download_rights`.`album` = " + albumIdAll);
        SQL.execute("DELETE FROM `album_images` WHERE `album_images`.`album` = " + albumIdAll);
        SQL.execute("DELETE FROM `favorites` WHERE `favorites`.`album` = " + albumIdAll);
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + albumIdSome);
        SQL.execute("DELETE FROM `download_rights` WHERE `download_rights`.`album` = " + albumIdSome);
        SQL.execute("DELETE FROM `album_images` WHERE `album_images`.`album` = " + albumIdSome);
        SQL.execute("DELETE FROM `favorites` WHERE `favorites`.`album` = " + albumIdSome);
        SQL.execute("DELETE FROM `albums` WHERE `albums`.`id` = " + badAlbumId);
        for (String file : files) {
            File imageFile = new File("content/albums/" + file);
            imageFile.delete();
        }
    }

    @Test(groups = {"api", "download-selected-images"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/download-selected-images.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"What to download is required!\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images"})
    public void emptyWhatTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "");
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"What to download cannot be blank!\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images"})
    public void noAlbumTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "some-file");
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"Album to download from is required!\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images"})
    public void emptyAlbumTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "some-file");
        data.put("album", "");
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"Album to download from cannot be blank!\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images"})
    public void badAlbumTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "some-file");
        data.put("album", 9999999);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"Album doesn't exist!\"}");
        // verify no issues
        finish();
    }

    //TODO - need to expand on users that are logged in
    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void noAlbumAccessTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "some-file");
        data.put("album", badAlbumId);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - need to expand repeat the below another 2 times (once for download user logged in, once for admin user logged in)
    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void allPossibleAllDesiredTest(ITestContext test) throws IOException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "all");
        data.put("album", albumIdAll);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-all " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Arrays.asList("file.1.png", "file.2.png", "file.3.png", "file.4.png", "file.5.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void allPossibleThreeFavoriteDesired(ITestContext test) throws IOException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdAll + "', '2');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdAll + "', '3');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdAll + "', '4');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdAll);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-all " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Arrays.asList("file.2.png", "file.3.png", "file.4.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void allPossibleOneFavoriteDesired(ITestContext test) throws IOException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdAll + "', '2');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdAll);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-all " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Collections.singletonList("file.2.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void allPossibleNoFavoriteDesired() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdAll);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void allPossibleOneDesired(ITestContext test) throws IOException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "2");
        data.put("album", albumIdAll);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-all " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Collections.singletonList("file.2.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void SomeOverlapPossibleAllDesiredTest(ITestContext test) throws IOException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "all");
        data.put("album", albumIdSome);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-some " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Arrays.asList("file.2.png", "file.3.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void SomeOverlapPossibleThreeFavoriteDesired(ITestContext test) throws IOException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '2');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '3');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '4');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-some " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Arrays.asList("file.2.png", "file.3.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void SomeOverlapPossibleOneFavoriteDesired(ITestContext test) throws IOException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '2');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-some " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Collections.singletonList("file.2.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void SomeOverlapPossibleNoFavoriteDesired() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void SomeOverlapPossibleOneDesired(ITestContext test) throws IOException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "2");
        data.put("album", albumIdSome);
        Date date = new Date(System.currentTimeMillis());
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"file\":\"..\\/tmp\\/sample-album-download-some " + formatter.format(date) + ".zip\"}");
        String fileUrl = response.getObjectData().get("file").getAsString().replaceAll(" ", "%20");
        // verify the file
        FileUtils.copyURLToFile(new URL(Property.getAppURL(this.getClass().getName(), test) + "api/" + fileUrl), new File("sample-album-download.zip"));
        assertZipContains("sample-album-download.zip", Collections.singletonList("file.2.png"));
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void NoOverlapPossibleThreeFavoriteDesired() throws UnknownHostException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '1');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '4');");
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '5');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void NoOverlapPossibleOneFavoriteDesired() throws UnknownHostException {
        //setup my favorites
        SQL.execute("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('" + getLocalHostLANAddress().getHostAddress() + "', '" + albumIdSome + "', '1');");

        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void NoOverlapPossibleNoFavoriteDesired() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "favorites");
        data.put("album", albumIdSome);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "download-selected-images", "needs-album"})
    public void NoOverlapPossibleOneDesired() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("what", "5");
        data.put("album", albumIdSome);
        Response response = call.post("api/download-selected-images.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("{\"err\":\"There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.\"}");
        // verify no issues
        finish();
    }
}
