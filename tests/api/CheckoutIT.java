import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.google.gson.JsonObject;
import org.testng.annotations.Test;

public class CheckoutIT extends SelenifiedBase {

    @Test(groups = {"api", "checkout"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/checkout.php", new Request());
        response.assertEquals().code(200);
        JsonObject json = new JsonObject();
        json.addProperty("error", "User must be logged in to submit their order.");
        response.assertEquals().objectData(json);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
