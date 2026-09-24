var Posts = createPostPreviewLoader(
    "/api/get-blogs-details.php",
    function (data) {
        return data.data;
    }
);
