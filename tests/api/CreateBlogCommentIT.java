import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.net.UnknownHostException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;

public class CreateBlogCommentIT extends SelenifiedBase {

    private int blogId = 9999;
    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.0");

    @BeforeMethod(groups = {"needs-post"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `blog_details` (`id`, `title`, `preview`, `date`, `offset`) VALUES ('" + blogId + "', 'sample blog post', '', '2019-10-18', 0);");
    }

    @AfterMethod(groups = {"needs-post"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `blog_details` WHERE `blog_details`.`id` = " + blogId);
        SQL.execute("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = " + blogId);
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/create-blog-comment.php", new Request());
        response.assertEquals().code(200);
        response.assertEquals().message("Post id is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void blankPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Post id cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment"})
    public void badPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "999999999");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("That ID doesn't match any posts");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void noMessage() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        response.assertEquals().code(200);
        response.assertEquals().message("Message is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void AllGood() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.assertEquals().code(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkDbEquals(null, rs, "user");
            checkDbEquals("", rs, "name");
            checkDbEquals(formatter.format(date), rs, "date");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "ip");
            checkDbEquals("", rs, "email");
            checkDbEquals("some message", rs, "comment");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithName() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("name", "some name");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.assertEquals().code(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkDbEquals(null, rs, "user");
            checkDbEquals("some name", rs, "name");
            checkDbEquals(formatter.format(date), rs, "date");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "ip");
            checkDbEquals("", rs, "email");
            checkDbEquals("some message", rs, "comment");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithEmail() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("email", "max@max.max");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.assertEquals().code(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkDbEquals(null, rs, "user");
            checkDbEquals("", rs, "name");
            checkDbEquals(formatter.format(date), rs, "date");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "ip");
            checkDbEquals("max@max.max", rs, "email");
            checkDbEquals("some message", rs, "comment");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithAll() throws SQLException, UnknownHostException {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("name", "max");
        data.put("email", "max@max.max");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.assertEquals().code(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkDbEquals(null, rs, "user");
            checkDbEquals("max", rs, "name");
            checkDbEquals(formatter.format(date), rs, "date");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "ip");
            checkDbEquals("max@max.max", rs, "email");
            checkDbEquals("some message", rs, "comment");
        }
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "create-blog-comment", "needs-post"})
    public void blogWithLoggedInAll() throws SQLException, UnknownHostException {
        //TODO - finish once login is figured out
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        data.put("name", "max");
        data.put("email", "max@max.max");
        data.put("message", "some message");
        Response response = call.post("api/create-blog-comment.php", new Request().setMultipartData(data));
        Date date = new Date(System.currentTimeMillis());
        response.assertEquals().code(200);
        ResultSet rs = SQL.select("SELECT * FROM `blog_comments` WHERE `blog` = " + blogId);
        while (rs.next()) {
            checkDbEquals(null, rs, "user");
            checkDbEquals("max", rs, "name");
            checkDbEquals(formatter.format(date), rs, "date");
            checkDbMatches("^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$", rs, "ip");
            checkDbEquals("max@max.max", rs, "email");
            checkDbEquals("some message", rs, "comment");
        }
        // verify no issues
        finish();
    }
}
