import com.coveros.selenified.Browser.BrowserUse;
import com.coveros.selenified.Selenified;
import com.coveros.selenified.exceptions.InvalidBrowserException;
import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.google.gson.JsonObject;
import org.testng.ITestContext;
import org.testng.ITestResult;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.lang.reflect.Method;
import java.net.MalformedURLException;
import java.util.HashMap;
import java.util.Map;

public class ContactMeIT extends Selenified {

    @BeforeClass(alwaysRun = true)
    public void beforeClass(ITestContext test) {
        setTestSite(this, test, "http://localhost/");
        //todo, upgrade to 3.2.0, and use new setContentType
    }

    @BeforeMethod(alwaysRun = true, groups = {"api", "contact-me"})
    protected void startTest(Object[] dataProvider, Method method, ITestContext test, ITestResult result) throws InvalidBrowserException, MalformedURLException {
        super.startTest(dataProvider, method, test, result, BrowserUse.FALSE);
    }

    @Test
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("/api/contact-me.php", new Request());
        response.assertEquals(200);
        response.assertEquals("Name is required");
        // verify no issues
        finish();
    }
//
//    @Test
//    public void onlyNameTest() {
//        Call call = this.calls.get();
//        Map data = new HashMap();
//        data.put("name", "Max");
//        Response response = call.post("/api/contact-me.php", new Request().setMultipartData(data));
//        response.assertEquals(200);
//        response.assertEquals("Phone is required");
//        // verify no issues
//        finish();
//    }
}
