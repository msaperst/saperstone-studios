import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

public class DeleteBlogIT extends BaseBrowserless {

    @Test(groups = {"api", "delete-blog"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/delete-blog.php", new Request());
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out
}
