$.fn.isOnScreen = function() {
	var element = this.get(0);
	var bounds = element.getBoundingClientRect();
	return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function PostsFull(totalPosts,tag) {
	this.loaded = 0;
	this.totalPosts = totalPosts;
	this.tag = tag;

	this.loadPosts();
}

PostsFull.prototype.loadPosts = function() {
	var PostsFull = this;
	$.get("/api/get-blogs-full.php", {
		start : PostsFull.loaded,
		tag: PostsFull.tag
	}, function(data) {
		// create our holding div
		var holder = $('<div>');
		
		// setup our title
		var title_row = $('<div>');
		title_row.addClass('row');
		var title = $('<div>');
		title.addClass('col-md-12 text-center');
		var h2 = $('<h2>');
		h2.append(data.title);
		title.append(h2);
		title_row.append(title);
		holder.append(title_row);
		
		// setup our post details 
		var details_row = $('<div>');
		details_row.addClass('row');
		var details_tags = $('<div>');
		details_tags.addClass('col-md-4 text-left');
		$.each(data.tags, function(k, v) {
			var tag_link = $('<a>');
			tag_link.attr('href', '/blog/category.php?t=' + v.id);
			tag_link.html(v.tag);
			details_tags.append(tag_link);
			if (k < data.tags.length - 1) {
				details_tags.append(", ");
			}
		});
		details_row.append(details_tags);
		var details_date = $('<div>');
		details_date.addClass('col-md-4 text-center');
		details_date.append("<strong>"+data.date+"</strong>");
		details_row.append(details_date);
		var details_likes = $('<div>');
		details_likes.addClass('col-md-4 text-right');
		details_row.append(details_likes);
		holder.append(details_row);
		
		// setup our post content
		$.each(data.content, function(k, v) {
			var row = $('<div>');
			row.addClass('row');

			var content = $('<div>');
			// if it's a text content
			if (v[0].hasOwnProperty('text')) {
				content.addClass('post-text col-md-12');
				$.each(v, function(l, w) {
					content.append(w.text);
				});
			} else {
				content.addClass('post-images col-md-12');
				var max_height = 0;
				$.each(v, function(l, w) {
					var image = $('<img>');
					image.addClass('post-image');
					image.attr('src', w.location);
					image.css({
						'height' : w.height + 'px',
						'width' : w.width + 'px',
						'left' : parseFloat(w.left) + 15 + 'px',
						'top' : w.top + 'px'
					});
					content.append(image);
					max_height = Math.max(max_height,
							(parseFloat(w.top) + parseFloat(w.height)));
				});
				content.css({
					'height' : max_height + 'px'
				});

				// setup our protecting div
				var protect = $('<div>');
				protect.addClass('post-protects');
				protect.css({
					'height' : max_height + 'px'
				});
				
				var image = $('<img>');
				image.addClass('post-protect');
				image.attr('src','/img/image.png');
				protect.append(image);
				row.append(protect);
			}
			row.append(content);
			holder.append(row);
		});
		
		$('#post-content').append(holder);
		
//		holder.addClass('post hovereffect');
//		holder.height($('.col-gallery').width() / 1.7);
//		// create our image
//		holder.css({
//			'background-image' : 'url("' + v.preview + '")',
//			'background-position' : '0px ' + v.offset + 'px',
//			'background-size' : $('.col-gallery').width() + 'px',
//		});
//		// create our overlay
//		var overlay = $('<div>');
//		overlay.addClass('overlay');
//		// our post title
//		var title = $('<h2>');
//		title.html(v.title);
//		// our view link
//		var link = $('<a>');
//		link.addClass('info no-border');
//		link.attr('href', '/blog/post.php?p=' + v.id);
//		// add our image icon
//		var view = $('<i>');
//		view.addClass('fa fa-search fa-2x');
//		// put them all together
//		link.append(view);
//		overlay.append(title);
//		overlay.append("<br/>");
//		overlay.append(link);
//		holder.append(overlay);
//		$('#col-' + k).append(holder);
		// when we done, see if we need to load more
		if ($('footer').isOnScreen()) {
			PostsFull.loadPosts();
		}
	}, "json");
	PostsFull.loaded++;
	return PostsFull.loaded;
};