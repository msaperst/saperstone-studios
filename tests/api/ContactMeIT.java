import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class ContactMeIT extends SelenifiedBase {

    @Test(groups = {"api", "contact-me"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/contact-me.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().message("Name is required");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "contact-me"})
    public void onlyNameTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Phone is required");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "contact-me"})
    public void onlyNamePhoneTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Email is required");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "contact-me"})
    public void onlyNamePhoneEmailTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@gmail.com");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("A message is required");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "contact-me"})
    public void allTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@gmail.com");
        data.put("message", "Hi There! I am a test email");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "contact-me"})
    public void allTestSS() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@saperstonestudios.com");
        data.put("message", "Hi There! I am another test email");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.");
        // verify no issues
        finish();
    }
}
