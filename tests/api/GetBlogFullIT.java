import com.coveros.selenified.services.Call;
import com.coveros.selenified.services.Request;
import com.coveros.selenified.services.Response;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import java.util.HashMap;
import java.util.Map;

public class GetBlogFullIT extends BaseBrowser {

    private int blogId = 9998;
    private int blogIdAll = 9999;

    @BeforeMethod(groups = {"needs-post"})
    public void createAlbum() {
        SQL.execute("INSERT INTO `blog_details` (`id`, `title`, `preview`, `date`, `offset`) VALUES ('" + blogId + "', 'sample blog post', '', '2019-10-18', 0);");
        SQL.execute("INSERT INTO `blog_details` (`id`, `title`, `preview`, `date`, `offset`) VALUES ('" + blogIdAll + "', 'sample blog post', '', '2019-10-18', 0);");
        SQL.execute("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('" + blogIdAll + "', 2, 'file-1.png', '600', '400', '0', '0');");
        SQL.execute("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('" + blogIdAll + "', 2, 'file-2.png', '600', '400', '0', '400');");
        SQL.execute("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('" + blogIdAll + "', 1, '<p>some text</p>');");
        SQL.execute("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('" + blogIdAll + "', 34);");
        SQL.execute("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('" + blogIdAll + "', 42);");
        SQL.execute("INSERT INTO `blog_comments` (`id`, `blog`, `name`, `date`, `ip`, `email`, `comment`) " +
                "VALUES ('" + blogIdAll + "', '" + blogIdAll + "', '', '2019-10-22 00:00:00', '192.168.1.1', '', 'Some comment');");
    }

    @AfterMethod(groups = {"needs-post"}, alwaysRun = true)
    public void deleteAlbum() {
        SQL.execute("DELETE FROM `blog_details` WHERE `blog_details`.`id` = " + blogId);
        SQL.execute("DELETE FROM `blog_details` WHERE `blog_details`.`id` = " + blogIdAll);
        SQL.execute("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = " + blogIdAll);
        SQL.execute("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = " + blogIdAll);
        SQL.execute("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = " + blogIdAll);
        SQL.execute("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = " + blogIdAll);
    }

    @Test(groups = {"api", "get-blog-full"})
    public void emptyParamsTest() {
        Call call = this.calls.get();
        Response response = call.get("api/get-blog-full.php", new Request());
        response.azzert().equals(200);
        response.azzert().equals("Post id is required!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-blog-full"})
    public void blankPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "");
        Response response = call.get("api/get-blog-full.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        response.azzert().equals("Post id cannot be blank!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-blog-full"})
    public void badPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", "999999999");
        Response response = call.get("api/get-blog-full.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        response.azzert().equals("Blog doesn't exist!");
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-blog-full", "needs-post"})
    public void goodPostId() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogId);
        Response response = call.get("api/get-blog-full.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        JsonObject json = new JsonObject();
        json.addProperty("id", String.valueOf(blogId));
        json.addProperty("title", "sample blog post");
        json.add("safe_title", null);
        json.addProperty("date", "October 18th, 2019");
        json.addProperty("preview", "");
        json.addProperty("offset", "0");
        json.addProperty("active", "0");
        json.addProperty("twitter", "0");
        response.azzert().equals(json);
        // verify no issues
        finish();
    }

    @Test(groups = {"api", "get-blog-full", "needs-post"})
    public void goodPostIdAll() {
        Call call = this.calls.get();
        Map<String, Object> data = new HashMap<>();
        data.put("post", blogIdAll);
        Response response = call.get("api/get-blog-full.php", new Request().setUrlParams(data));
        response.azzert().equals(200);
        JsonObject json = new JsonObject();
        json.addProperty("id", String.valueOf(blogIdAll));
        json.addProperty("title", "sample blog post");
        json.add("safe_title", null);
        json.addProperty("date", "October 18th, 2019");
        json.addProperty("preview", "");
        json.addProperty("offset", "0");
        json.addProperty("active", "0");
        json.addProperty("twitter", "0");
        JsonObject text = new JsonObject();
        text.addProperty("blog", "9999");
        text.addProperty("contentGroup", "1");
        text.addProperty("text", "<p>some text</p>");
        JsonArray textArray = new JsonArray();
        textArray.add(text);
        JsonObject file1 = new JsonObject();
        file1.addProperty("blog", "9999");
        file1.addProperty("contentGroup", "2");
        file1.addProperty("location", "file-1.png");
        file1.addProperty("width", "600");
        file1.addProperty("height", "400");
        file1.addProperty("left", "0");
        file1.addProperty("top", "0");
        JsonObject file2 = new JsonObject();
        file2.addProperty("blog", "9999");
        file2.addProperty("contentGroup", "2");
        file2.addProperty("location", "file-2.png");
        file2.addProperty("width", "600");
        file2.addProperty("height", "400");
        file2.addProperty("left", "0");
        file2.addProperty("top", "400");
        JsonArray fileArray = new JsonArray();
        fileArray.add(file1);
        fileArray.add(file2);
        JsonObject content = new JsonObject();
        content.add("2", fileArray);
        content.add("1", textArray);
        json.add("content", content);
        JsonObject tag1 = new JsonObject();
        tag1.addProperty("id", "34");
        tag1.addProperty("tag", "1st Birthday");
        JsonObject tag2 = new JsonObject();
        tag2.addProperty("id", "42");
        tag2.addProperty("tag", "Family Reunion");
        JsonArray tagArray = new JsonArray();
        tagArray.add(tag1);
        tagArray.add(tag2);
        json.add("tags", tagArray);
        JsonObject comment = new JsonObject();
        comment.addProperty("id", String.valueOf(blogIdAll));
        comment.addProperty("blog", "9999");
        comment.add("user", null);
        comment.addProperty("name", "");
        comment.addProperty("date", "2019-10-22 00:00:00");
        comment.addProperty("ip", "192.168.1.1");
        comment.addProperty("email", "");
        comment.addProperty("comment", "Some comment");
        JsonArray commentArray = new JsonArray();
        commentArray.add(comment);
        json.add("comments", commentArray);
        response.azzert().equals(json);
        // verify no issues
        finish();
    }

    //TODO - once login is figured out, need to add test to determine if comment can be deleted
}
