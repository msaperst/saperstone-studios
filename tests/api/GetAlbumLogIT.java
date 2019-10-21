import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class GetAlbumLogIT extends BaseBrowserless {

    @Test(groups = {"api", "get-album-log"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/get-album-log.php", new Request());
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out

//    @Test(groups = {"api", "delete-album"})
//    public void emptyParamsTest() {
//        Call call = this.calls.get();
//        Response response = call.post("api/delete-album.php", new Request());
//        response.azzert().equals(200);
//        response.azzert().equals("Album id is required!");
//        // verify no issues
//        finish();
//    }
//
//    @Test(groups = {"api", "delete-album"})
//    public void blankAlbumTest() {
//        Call call = this.calls.get();
//        Map<String, Object> data = new HashMap<>();
//        data.put("id", "");
//        Response response = call.post("api/delete-album.php", new Request());
//        response.azzert().equals(200);
//        response.azzert().equals("Album id cannot be blank!");
//        // verify no issues
//        finish();
//    }
//
//    @Test(groups = {"api", "delete-album"})
//    public void badAlbumTest() {
//        Call call = this.calls.get();
//        Map<String, Object> data = new HashMap<>();
//        data.put("id", "999999999");
//        Response response = call.post("api/delete-album.php", new Request());
//        response.azzert().equals(200);
//        response.azzert().equals("That ID doesn't match any albums");
//        // verify no issues
//        finish();
//    }
}
