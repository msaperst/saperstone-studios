import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class CreateAlbumIT extends SelenifiedBase {

    @Test(groups = {"api", "create-album"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/create-album.php", new Request());
        response.assertEquals().code(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
