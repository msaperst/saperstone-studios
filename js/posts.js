$.fn.isOnScreen = function() {
	var element = this.get(0);
	var bounds = element.getBoundingClientRect();
	return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Posts(columns, totalImages) {
	this.loaded = 0;
	this.columns = columns;
	this.totalImages = totalImages;

	this.loadImages();
}

Posts.prototype.loadImages = function() {
	var Posts = this;
	$.get("/api/get-blogs-details.php", {
		start : Posts.loaded,
		howMany : Posts.columns
	}, function(data) {
		// load each of our 4 images on the screen
		$.each(data, function(k, v) {
			// create our holding div
			var holder = $('<div>');
			holder.addClass('post hovereffect');
			holder.height($('.col-gallery').width() / 1.7);
			// create our image
			holder.css({
				'background-image' : 'url("' + v.preview + '")',
				'background-position' : '0px ' + v.offset + 'px',
				'background-size' : $('.col-gallery').width() + 'px',
			});
			// create our overlay
			var overlay = $('<div>');
			overlay.addClass('overlay');
			// our post title
			var title = $('<h2>');
			title.html(v.title);
			// our view link
			var link = $('<a>');
			link.addClass('info no-border');
			link.attr('href', '/blog/post.php?p=' + v.id);
			// add our image icon
			var view = $('<i>');
			view.addClass('fa fa-search fa-2x');
			// put them all together
			link.append(view);
			overlay.append(title);
			overlay.append("<br/>");
			overlay.append(link);
			holder.append(overlay);
			$('#col-' + k).append(holder);
		});
		// when we done, see if we need to load more
		if ($('footer').isOnScreen()) {
			Posts.loadImages();
		}
	}, "json");
	Posts.loaded += Posts.columns;
	return Posts.loaded;
};