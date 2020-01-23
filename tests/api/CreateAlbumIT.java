import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class CreateAlbumIT extends SelenifiedBase {

    @Test(groups = {"api", "create-album"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/create-album.php", new Request());
        response.assertEquals().code(401);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-album"})
    public void noAlbumName() {
        Call call = this.calls.get();
        call.addHeaders(loginCookie);
        Response response = call.post("api/create-album.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().message("Album name is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-album"})
    public void emptyAlbumName() {
        Call call = this.calls.get();
        call.addHeaders(loginCookie);
        Map<String, Object> data = new HashMap<>();
        data.put("name", "");
        Response response = call.post("api/create-album.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Album name is required!");
        // verify no issues
        finish();
    }

    //TODO finish now that we have solved the login!
}
