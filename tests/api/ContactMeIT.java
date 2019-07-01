import com.coveros.selenified.Browser.BrowserUse;
import com.coveros.selenified.Selenified;
import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.HTTP;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.ITestContext;
import org.testng.ITestResult;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.io.IOException;
import java.lang.reflect.Method;
import java.util.HashMap;
import java.util.Map;

public class ContactMeIT extends Selenified {

    @BeforeClass(alwaysRun = true)
    public void beforeClass(ITestContext test) {
        setAppURL(this, test, "http://localhost/");
        setContentType(this, test, HTTP.ContentType.FORMDATA);
    }

    @BeforeMethod(alwaysRun = true, groups = {"api", "contact-me"})
    protected void startTest(Object[] dataProvider, Method method, ITestContext test, ITestResult result) throws IOException {
        super.startTest(dataProvider, method, test, result, BrowserUse.FALSE);
    }

    @Test
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/contact-me.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("Name is required");
        // verify no issues
        finish();
    }

    @Test
    public void onlyNameTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Phone is required");
        // verify no issues
        finish();
    }

    @Test
    public void onlyNamePhoneTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Email is required");
        // verify no issues
        finish();
    }

    @Test
    public void onlyNamePhoneEmailTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@gmail.com");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("A message is required");
        // verify no issues
        finish();
    }

    @Test
    public void allTest() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@gmail.com");
        data.put("message", "Hi There! I am a test email");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.");
        // verify no issues
        finish();
    }

    @Test
    public void allTestSS() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("name", "Max");
        data.put("phone", "571-245-3351");
        data.put("email", "msaperst@saperstonestudios.com");
        data.put("message", "Hi There! I am another test email");
        Response response = call.post("api/contact-me.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Thank you for submitting your comment. We greatly appreciate your interest and feedback. Someone will get back to you within 24 hours.");
        // verify no issues
        finish();
    }
}
