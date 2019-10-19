import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.google.gson.JsonObject;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class CheckoutIT extends BaseBrowserless {

    @Test(groups = {"api", "checkout"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/checkout.php", new Request());
        response.azzert().equals(200);
        JsonObject json = new JsonObject();
        json.addProperty("error", "User must be logged in to submit their order.");
        response.azzert().equals(json);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
