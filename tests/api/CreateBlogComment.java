import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;

public class CreateBlogComment extends BaseBrowser {

    private int blogId = 9999;
    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.0");


    @BeforeMethod(groups = {"needs-post"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `blog_details` (`id`, `title`, `preview`, `date`, `offset`) VALUES (" + blogId + ", 'sample blog post', '', '2019-10-18', 0));");
    }

    @AfterMethod(groups = {"needs-post"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `blog_details` WHERE `albums`.`id` = " + blogId);
        SQL.execute("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = " + blogId);
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/create-blog-comment.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("Post id is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void blankPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Post id cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void badPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "999999999");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("That ID doesn't match any posts");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void noMessageId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.azzert().equals(200);
        response.azzert().equals("Message is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void AllGoodId() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.azzert().equals(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkUser(null, rs);
            checkName("", rs);
            checkDate(formatter.format(date), rs);
            checkIp("172.25.0.1", rs);
            checkEmail("", rs);
            checkComment("some message", rs);
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithName() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("name", "some name");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.azzert().equals(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkUser(null, rs);
            checkName("some name", rs);
            checkDate(formatter.format(date), rs);
            checkIp("172.25.0.1", rs);
            checkEmail("", rs);
            checkComment("some message", rs);
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithEmail() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("email", "max@max.max");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.azzert().equals(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkUser(null, rs);
            checkName("", rs);
            checkDate(formatter.format(date), rs);
            checkIp("172.25.0.1", rs);
            checkEmail("max@max.max", rs);
            checkComment("some message", rs);
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithAll() throws SQLException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("name", "max");
        data.put("email", "max@max.max");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.azzert().equals(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkUser(null, rs);
            checkName("max", rs);
            checkDate(formatter.format(date), rs);
            checkIp("172.25.0.1", rs);
            checkEmail("max@max.max", rs);
            checkComment("some message", rs);
        }
        // verify no issues
        finish();
    }


    //TODO - finish once login is figured out

    private void checkUser(String expectedUser, ResultSet rs) throws SQLException {
        String user = rs.getString("user");
        if (user == expectedUser || expectedUser.equals(user)) {
            this.apps.get().getReporter().pass("", "DB Results contain user '" + expectedUser + "'", "DB Results contain user '" + user + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain user '" + expectedUser + "'", "DB Results contain user '" + user + "'");
        }
    }

    private void checkName(String expectedName, ResultSet rs) throws SQLException {
        String name = rs.getString("name");
        if (expectedName.equals(name)) {
            this.apps.get().getReporter().pass("", "DB Results contain name '" + expectedName + "'", "DB Results contain name '" + name + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain name '" + expectedName + "'", "DB Results contain name '" + name + "'");
        }
    }

    private void checkDate(String expectedDate, ResultSet rs) throws SQLException {
        String date = rs.getString("date");
        if (expectedDate.equals(date)) {
            this.apps.get().getReporter().pass("", "DB Results contain date '" + expectedDate + "'", "DB Results contain date '" + date + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain date '" + expectedDate + "'", "DB Results contain date '" + date + "'");
        }
    }

    private void checkIp(String expectedIp, ResultSet rs) throws SQLException {
        String ip = rs.getString("ip");
        if (expectedIp.equals(ip)) {
            this.apps.get().getReporter().pass("", "DB Results contain ip '" + expectedIp + "'", "DB Results contain ip '" + ip + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain ip '" + expectedIp + "'", "DB Results contain ip '" + ip + "'");
        }
    }

    private void checkEmail(String expectedEmail, ResultSet rs) throws SQLException {
        String email = rs.getString("email");
        if (expectedEmail.equals(email)) {
            this.apps.get().getReporter().pass("", "DB Results contain email '" + expectedEmail + "'", "DB Results contain email '" + email + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain email '" + expectedEmail + "'", "DB Results contain email '" + email + "'");
        }
    }

    private void checkComment(String expectedComment, ResultSet rs) throws SQLException {
        String comment = rs.getString("comment");
        if (expectedComment.equals(comment)) {
            this.apps.get().getReporter().pass("", "DB Results contain comment '" + expectedComment + "'", "DB Results contain comment '" + comment + "'");
        } else {
            this.apps.get().getReporter().fail("", "DB Results contain comment '" + expectedComment + "'", "DB Results contain comment '" + comment + "'");
        }
    }
}
