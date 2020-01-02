import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class CropImageIT extends SelenifiedBase {

    @Test(groups = {"api", "crop-image"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/crop-image.php", new Request());
        response.assertEquals().code(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
