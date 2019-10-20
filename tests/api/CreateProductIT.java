import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class CreateProductIT extends BaseBrowserless {

    @Test(groups = {"api", "create-product"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/create-product.php", new Request());
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
