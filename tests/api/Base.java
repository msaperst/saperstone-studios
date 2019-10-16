import com.coveros.selenified.Browser;
import com.coveros.selenified.Selenified;
import com.coveros.selenified.services.HTTP;
import org.testng.ITestContext;
import org.testng.ITestResult;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;

import java.io.IOException;
import java.lang.reflect.Method;

public class Base extends Selenified {

    @BeforeClass(alwaysRun = true)
    public void beforeClass(ITestContext test) {
        setAppURL(this, test, "http://localhost:90/");
        setContentType(this, test, HTTP.ContentType.FORMDATA);
    }

    @BeforeMethod(alwaysRun = true)
    protected void startTest(Object[] dataProvider, Method method, ITestContext test, ITestResult result) throws IOException {
        super.startTest(dataProvider, method, test, result, Browser.BrowserUse.FALSE);
    }
}
