import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class GetAllFavoritesIT extends SelenifiedBase {

    @Test(groups = {"api", "get-all-favorites"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/get-all-favorites.php", new Request());
        response.assertEquals().code(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out

//    @Test(groups = {"api", "delete-album"})
//    public void emptyParamsTest() {
//        Call call = this.calls.get();
//        Response response = call.post("api/delete-album.php", new Request());
//        response.assertEquals().code(200);
//        response.assertEquals().message("Album id is required!");
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
//        response.assertEquals().code(200);
//        response.assertEquals().message("Album id cannot be blank!");
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
//        response.assertEquals().code(200);
//        response.assertEquals().message("That ID doesn't match any albums");
//        // verify no issues
//        finish();
//    }
}
