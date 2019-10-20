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

public class DeleteBlogComment extends BaseBrowser {

    @Test(groups = {"api", "delete-album"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.post("api/delete-blog-comment.php", new Request());
        response.azzert().equals(401);
        // verify no issues
        finish();
    }

    //TODO - finish once login is figured out

//    private int blogId = 9999;
//    SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.0");
//
//    @BeforeMethod(groups = {"needs-post"})
//    public void createAlbum() {
//        SQL.execute("INSERT INTO `blog_details` (`id`, `title`, `preview`, `date`, `offset`) VALUES ('" + blogId + "', 'sample blog post', '', '2019-10-18', 0);");
//        Date date = new Date(System.currentTimeMillis());
//        SQL.execute("INSERT INTO `blog_comments` (`blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) " +
//                "VALUES ('" + blogId + "', NULL, '', '" +formatter.format(date)+ "', '172.25.0.1', '', 'Some Comment');");
//    }
//
//    @AfterMethod(groups = {"needs-post"}, alwaysRun = true)
//    public void deleteAlbum() {
//        SQL.execute("DELETE FROM `blog_details` WHERE `blog_details`.`id` = " + blogId);
//        SQL.execute("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = " + blogId);
//    }
//
//    @Test(groups = {"api", "delete-blog-comment"})
//    public void emptyParamsTest() {
//        Call call = this.calls.get();
//        Response response = call.post("api/delete-blog-comment.php", new Request());
//        response.azzert().equals(200);
//        response.azzert().equals("Comment id is required!");
//        // verify no issues
//        finish();
//    }
//
//    @Test(groups = {"api", "delete-blog-comment"})
//    public void blankPostId() {
//        Call call = this.calls.get();
//        Map<String, Object> data = new HashMap<>();
//        data.put("comment", "");
//        Response response = call.post("api/delete-blog-comment.php", new Request().setMultipartData(data));
//        response.azzert().equals(200);
//        response.azzert().equals("Comment id cannot be blank!");
//        // verify no issues
//        finish();
//    }
//
//    @Test(groups = {"api", "delete-blog-comment"})
//    public void badPostId() {
//        Call call = this.calls.get();
//        Map<String, Object> data = new HashMap<>();
//        data.put("comment", "999999999");
//        Response response = call.post("api/delete-blog-comment.php", new Request().setMultipartData(data));
//        response.azzert().equals(200);
//        response.azzert().equals("That ID doesn't match any comments");
//        // verify no issues
//        finish();
//    }
}
