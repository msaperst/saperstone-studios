--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
-- INSERT IGNORE INTO `galleries` VALUES (0, NULL, NULL, 'Leigh Ann');
INSERT IGNORE INTO `galleries` VALUES (1, NULL, NULL, 'Portrait', NULL);
INSERT IGNORE INTO `galleries` VALUES (2, 1, 'maternity.jpg', 'Maternity', NULL);
INSERT IGNORE INTO `galleries` VALUES (3, 1, 'newborn.jpg', 'Newborn', NULL);
INSERT IGNORE INTO `galleries` VALUES (4, 1, '6-month.jpg', '6 Months', NULL);
INSERT IGNORE INTO `galleries` VALUES (5, 1, '1-year.jpg', 'First Birthday', NULL);
INSERT IGNORE INTO `galleries` VALUES (6, 1, 'family.jpg', 'Kids and Family', NULL);
INSERT IGNORE INTO `galleries` VALUES (7, 1, 'senior.jpg', 'Seniors', NULL);
INSERT IGNORE INTO `galleries` VALUES (8, NULL, NULL, 'Wedding', NULL);
INSERT IGNORE INTO `galleries` VALUES (9, 8, 'surprise.jpg', 'Surprise Proposals', NULL);
INSERT IGNORE INTO `galleries` VALUES (10, 8, 'engagement.jpg', 'Engagements', NULL);
INSERT IGNORE INTO `galleries` VALUES (11, 8, 'wedding.jpg', 'Weddings', NULL);
INSERT IGNORE INTO `galleries` VALUES (12, 8, 'night.jpg', 'Night Photography', NULL);
INSERT IGNORE INTO `galleries` VALUES (13, 3, 'newborn-fav.jpg', 'Favorites', NULL);
INSERT IGNORE INTO `galleries` VALUES (14, 3, 'home.jpg', 'At Your Home', NULL);
INSERT IGNORE INTO `galleries` VALUES (15, 3, 'studio.jpg', 'Studio', NULL);
INSERT IGNORE INTO `galleries` VALUES (16, NULL, NULL, 'Home Studio', NULL);
INSERT IGNORE INTO `galleries` VALUES (17, 9, 'nat-harbor.jpg', 'National Harbor', NULL);
INSERT IGNORE INTO `galleries` VALUES (18, 9, 'proposal-dc.jpg', 'DC Mall', NULL);
INSERT IGNORE INTO `galleries` VALUES (19, 9, 'georgetown.jpg', 'Georgetown', NULL);
INSERT IGNORE INTO `galleries` VALUES (20, 10, 'engagement-fav.jpg', 'Favorites', NULL);
INSERT IGNORE INTO `galleries` VALUES (21, 10, 'engagement-dc.jpg', 'Washington DC', NULL);
INSERT IGNORE INTO `galleries` VALUES (22, 10, 'old-town.jpg', 'Old Town Alexandria', NULL);
INSERT IGNORE INTO `galleries` VALUES (23, 10, 'paint-war.jpg', 'Paint War', NULL);
INSERT IGNORE INTO `galleries` VALUES (24, 11, 'wedding-fav.jpg', 'Favorites', NULL);
INSERT IGNORE INTO `galleries` VALUES (25, 11, 'wedding-1.jpg', 'Wedding 1', NULL);
INSERT IGNORE INTO `galleries` VALUES (26, 11, 'wedding-2.jpg', 'Wedding 2', NULL);
INSERT IGNORE INTO `galleries` VALUES (27, 11, 'wedding-3.jpg', 'Wedding 3', NULL);
INSERT IGNORE INTO `galleries` VALUES (28, 1, NULL, 'Product', NULL);
INSERT IGNORE INTO `galleries` VALUES (29, 28, 'story-grid.jpg', 'Story Grids', 'Hate making sure your prints are evenly spaced once hung on the wall?  Then this is the art product for you.  Each story grid comes with a paper template to hang on the wall.  Your template indicates where to place metal pegs which then make up a grid system on your wall.  Simply slip the prints onto the metal pegs and voila! Evenly spaced prints! These collages range in overall sizes from 2'' x 3'' all the way up to 4.5'' x 2'' or beyond and are totally customizable. Images are printed on either metal or a lustre photographic paper, your choice.');
INSERT IGNORE INTO `galleries` VALUES (30, 28, 'keepsake-album.jpg', 'Heirloom Albums', 'How are you planning to share all those amazing memories with future little ones? Heirloom albums are the perfect way to put all your favorite images in one place. Hand made and printed Fuji lustre paper that has a 100+ year rating (the highest in the industry), these albums will be sure to stand the test of time for generations to come.');
INSERT IGNORE INTO `galleries` VALUES (31, 28, 'acrylic-print.jpg', 'Acrylic Prints', 'These gorgeous portraits are printed on a metallic paper and mounted under acrylic for a frameless modern way to display your images.  They stand out from the wall about 3/4 of an inch which gives depth.  One image can stand alone or order multiples to display a series from your session.');
INSERT IGNORE INTO `galleries` VALUES (32, 28, 'keepsake-box.jpg', 'Keepsake Boxes', 'Perfect for anyone who wants to display a lot of images but doesn''t have a ton of wall space.  These custom 5x7 boxes come with 10 of your favorite images from your session printed on lustre paper and mounted on a rigid black styrene.  Rotate through displaying your images on the included easel for all to enjoy.');
INSERT IGNORE INTO `galleries` VALUES (33, 28, 'stand-out-frame.jpg', 'Stand Out Frames', 'These lustre prints are mounted on 3/4 inch thick foam core and wrapped with either a black or white edge.  Modern, sleek and light weight for easy hanging!');
INSERT IGNORE INTO `galleries` VALUES (34, 28, 'canvas-print.jpg', 'Canvas Prints', 'That classic, timeless look of canvas can''t be beat. Archival quality stretched canvas over a solid wooden frame built to stand the test of time.  Hang just one or multiples to create a cluster of images that tell a story from your session.');
INSERT IGNORE INTO `galleries` VALUES (35, 4, '6-month-studio.jpg', 'In Studio', NULL);
INSERT IGNORE INTO `galleries` VALUES (36, 4, '6-month-location.jpg', 'On Location', NULL);
INSERT IGNORE INTO `galleries` VALUES (37, 8, 'photobooth.jpg', 'Photobooth', NULL);
INSERT IGNORE INTO `galleries` VALUES (38, 8, NULL, 'Product', NULL);
INSERT IGNORE INTO `galleries` VALUES (39, 38, 'story-grid.jpg', 'Story Grids', 'Hate making sure your prints are evenly spaced once hung on the wall?  Then this is the art product for you.  Each story grid comes with a paper template to hang on the wall.  Your template indicates where to place metal pegs which then make up a grid system on your wall.  Simply slip the prints onto the metal pegs and voila! Evenly spaced prints! These collages range in overall sizes from 2'' x 3'' all the way up to 4.5'' x 2'' or beyond and are totally customizable. Images are printed on either metal or a lustre photographic paper, your choice.');
INSERT IGNORE INTO `galleries` VALUES (40, 38, 'keepsake-album.jpg', 'Heirloom Albums', 'How are you planning to share all those amazing memories with future little ones? Heirloom albums are the perfect way to put all your favorite images in one place. Hand made and printed Fuji lustre paper that has a 100+ year rating (the highest in the industry), these albums will be sure to stand the test of time for generations to come.');
INSERT IGNORE INTO `galleries` VALUES (41, 38, 'acrylic-print.jpg', 'Acrylic Prints', 'These gorgeous portraits are printed on a metallic paper and mounted under acrylic for a frameless modern way to display your images.  They stand out from the wall about 3/4 of an inch which gives depth.  One image can stand alone or order multiples to display a series from your session.');
INSERT IGNORE INTO `galleries` VALUES (42, 38, 'keepsake-box.jpg', 'Keepsake Boxes', 'Perfect for anyone who wants to display a lot of images but doesn''t have a ton of wall space.  These custom 5x7 boxes come with 10 of your favorite images from your session printed on lustre paper and mounted on a rigid black styrene.  Rotate through displaying your images on the included easel for all to enjoy.');
INSERT IGNORE INTO `galleries` VALUES (43, 38, 'stand-out-frame.jpg', 'Stand Out Frames', 'These lustre prints are mounted on 3/4 inch thick foam core and wrapped with either a black or white edge.  Modern, sleek and light weight for easy hanging!');
INSERT IGNORE INTO `galleries` VALUES (44, 38, 'canvas-print.jpg', 'Canvas Prints', 'That classic, timeless look of canvas can''t be beat. Archival quality stretched canvas over a solid wooden frame built to stand the test of time.  Hang just one or multiples to create a cluster of images that tell a story from your session.');
INSERT IGNORE INTO `galleries` VALUES (45, 40, 'standard-albums.jpg', 'Standard Albums', NULL);
INSERT IGNORE INTO `galleries` VALUES (46, 40, 'signature-albums.jpg', 'Signature Albums', NULL);
INSERT IGNORE INTO `galleries` VALUES (47, 40, 'engagement-book.jpg', 'Engagement Book', NULL);
INSERT IGNORE INTO `galleries` VALUES (48, 5, '1year-studio.jpg', 'Studio Sessions', NULL);
INSERT IGNORE INTO `galleries` VALUES (49, 5, '1year-locaion.jpg', 'On Location', NULL);
INSERT IGNORE INTO `galleries` VALUES (50, 28, 'album-block.jpg', 'Album Block', 'This wooden block holds 10 of your favorite 5x7 images and is perfect for display on a mantle, coffee table or shelf.  Photos are mounted on a durable styrene, making it easy to rotate through displaying your images.  Color options are black or white, your choice!');
INSERT IGNORE INTO `galleries` VALUES (51, 28, 'keepsake-usb.jpg', 'Keepsake USB', 'Every time you order digital files, they are given to you in this keepsake USB case for safe keeping.  Be sure to back these images up in multiple locations as digital media is forever changing!');
INSERT IGNORE INTO `galleries` VALUES (52, NULL, NULL, 'Commercial', NULL);
INSERT IGNORE INTO `galleries` VALUES (53, 52, 'studio-headshots.jpg', 'Studio Headshots', NULL);
INSERT IGNORE INTO `galleries` VALUES (54, 52, 'on-location-headshots.jpg', 'On Location Headshots', NULL);
INSERT IGNORE INTO `galleries` VALUES (55, 52, 'company-headshots.jpg', 'Company Headshots and Team Photos', NULL);
INSERT IGNORE INTO `galleries` VALUES (56, 52, 'professional-branding.jpg', 'Professional Branding', 'Move away from the ''stock photo'' look for your website, social media and branding materials and give your business a more personal voice. Below are some past projects we''ve done to showcase different companies and their individual corporate culture.');
INSERT IGNORE INTO `galleries` VALUES (57, 52, 'events.jpg', 'Events', NULL);
INSERT IGNORE INTO `galleries` VALUES (58, 52, 'photobooth.jpg', 'Photobooth', NULL);

INSERT IGNORE INTO `galleries` VALUES (62, 28, 'reveal-box.jpg', 'Reveal Box', 'Hand made in Italy with a wide array of color options in leatherette to customize to your home and style. The Reveal Box holds 15 of your favorite images from your session on fine art paper either printed on a thick styrene at 8x10 or printed as 5x7 and placed in an 8x10 matte - Mix and match however you choose.<br/><br/>This box also allows for display versatility. Place your favorite image on top to be viewed through the glass window, place images in an 8x10 frame and put on your walls or tabletop or even place images as is on an easel. These images can go from box to frame and back again with extreme ease.');
INSERT IGNORE INTO `galleries` VALUES (63, 38, 'reveal-box.jpg', 'Reveal Box', 'Hand made in Italy with a wide array of color options in leatherette to customize to your home and style. The Reveal Box holds 15 of your favorite images from your session on fine art paper either printed on a thick styrene at 8x10 or printed as 5x7 and placed in an 8x10 matte - Mix and match however you choose.<br/><br/>This box also allows for display versatility. Place your favorite image on top to be viewed through the glass window, place images in an 8x10 frame and put on your walls or tabletop or even place images as is on an easel. These images can go from box to frame and back again with extreme ease.');
INSERT IGNORE INTO `galleries` VALUES (65, 56, 'team-hot-cocoa-social.jpg', 'Team Hot Cocoa Social', NULL);
INSERT IGNORE INTO `galleries` VALUES (66, 56, 'corporate-culture.jpg', 'Corporate Culture', NULL);
INSERT IGNORE INTO `galleries` VALUES (67, 56, 'doctor-care.jpg', 'Doctor Care', NULL);
INSERT IGNORE INTO `galleries` VALUES (68, 56, 'neurogrow-brain-fitness-center.jpg', 'NeuroGrow - Brain Fitness Center', NULL);
INSERT IGNORE INTO `galleries` VALUES (69, 57, 'company-meeting.jpg', 'Company Meeting', NULL);
INSERT IGNORE INTO `galleries` VALUES (70, 57, 'holiday-party.jpg', 'Holiday Party', NULL);
INSERT IGNORE INTO `galleries` VALUES (71, 57, 'corporate-picnic.jpg', 'Corporate Picnic', NULL);


/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;