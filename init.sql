-- ============================================
-- Test Database Schema for superviral.io
-- ============================================

USE etra_superviral;

-- Content table (used by header.php and index.php)
CREATE TABLE IF NOT EXISTS `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) DEFAULT 'sv',
  `country` varchar(10) DEFAULT 'us',
  `page` varchar(100) DEFAULT 'home',
  `name` varchar(100) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `idx_brand_country_page` (`brand`, `country`, `page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test content for home page
INSERT INTO `content` (`brand`, `country`, `page`, `name`, `content`) VALUES
('sv', 'us', 'home', 'metadesc', 'Buy Instagram followers, likes and views from Superviral - the #1 social media growth service.'),
('sv', 'us', 'home', 'title', 'Buy Instagram Followers | Superviral'),
('sv', 'us', 'home', 'canonical', 'https://superviral.io/'),
('sv', 'us', 'home', 'h1', 'Get More Instagram Followers'),
('sv', 'us', 'home', 'h2', 'Grow Your Social Media Presence'),
('sv', 'us', 'global', 'footersupport', 'Need help? Contact us at support@superviral.io'),
('sv', 'us', 'global', 'hsince', '2016'),
('sv', 'us', 'global', 'htitlemain', 'Instagram Services'),
('sv', 'us', 'global', 'htitlemain2', 'Resources'),
('sv', 'us', 'global', 'htitlemain3', 'Support'),
('sv', 'us', 'global', 'htitlemain4', 'Free Tools'),
('sv', 'us', 'global', 'htitlemain5', 'TikTok Services'),
('sv', 'us', 'global', 'hlink1', 'Buy Instagram Followers'),
('sv', 'us', 'global', 'hhref1', '/buy-instagram-followers/'),
('sv', 'us', 'global', 'htitle1', 'Buy Instagram Followers'),
('sv', 'us', 'global', 'hlink2', 'Buy Instagram Likes'),
('sv', 'us', 'global', 'hhref2', '/buy-instagram-likes/'),
('sv', 'us', 'global', 'htitle2', 'Buy Instagram Likes'),
('sv', 'us', 'global', 'hlink3', 'Buy Instagram Views'),
('sv', 'us', 'global', 'hhref3', '/buy-instagram-views/'),
('sv', 'us', 'global', 'htitle3', 'Buy Instagram Views'),
('sv', 'us', 'global', 'hlink4', 'Track My Order'),
('sv', 'us', 'global', 'hhref4', '/track-my-order/'),
('sv', 'us', 'global', 'htitle4', 'Track My Order'),
('sv', 'us', 'global', 'hlink5', 'FAQ'),
('sv', 'us', 'global', 'hhref5', '/faq/'),
('sv', 'us', 'global', 'htitle5', 'FAQ'),
('sv', 'us', 'global', 'hlink6', 'About Us'),
('sv', 'us', 'global', 'hhref6', '/about-us/'),
('sv', 'us', 'global', 'htitle6', 'About Us'),
('sv', 'us', 'global', 'hlink7', 'Contact Us'),
('sv', 'us', 'global', 'hhref7', '/contact-us/'),
('sv', 'us', 'global', 'htitle7', 'Contact Us'),
('sv', 'us', 'global', 'hlink8', 'Blog'),
('sv', 'us', 'global', 'hhref8', '/blog/'),
('sv', 'us', 'global', 'htitle8', 'Blog'),
('sv', 'us', 'global', 'hlink9', 'Terms of Service'),
('sv', 'us', 'global', 'hhref9', '/tos/'),
('sv', 'us', 'global', 'htitle9', 'Terms of Service'),
('sv', 'us', 'global', 'hlink10', 'My Account'),
('sv', 'us', 'global', 'hhref10', '/account/'),
('sv', 'us', 'global', 'htitle10', 'My Account'),
('sv', 'us', 'global', 'hlinkaccount', 'My Account'),
('sv', 'us', 'global', 'hlink11', 'Buy Instagram Comments'),
('sv', 'us', 'global', 'hhref11', '/buy-instagram-comments/'),
('sv', 'us', 'global', 'htitle11', 'Buy Instagram Comments'),
('sv', 'us', 'global', 'hlink13', 'Instagram Video Downloader'),
('sv', 'us', 'global', 'hhref13', '/instagram-video-downloader/'),
('sv', 'us', 'global', 'htitle13', 'Instagram Video Downloader'),
('sv', 'us', 'global', 'hlink14', 'Instagram Story Downloader'),
('sv', 'us', 'global', 'hhref14', '/instagram-story-downloader/'),
('sv', 'us', 'global', 'htitle14', 'Instagram Story Downloader'),
('sv', 'us', 'global', 'hlink15', 'Instagram Profile Viewer'),
('sv', 'us', 'global', 'hhref15', '/instagram-profile-picture-viewer/'),
('sv', 'us', 'global', 'htitle15', 'Instagram Profile Viewer'),
('sv', 'us', 'global', 'hlink16', 'Instagram Follower Counter'),
('sv', 'us', 'global', 'hhref16', '/instagram-follower-count/'),
('sv', 'us', 'global', 'htitle16', 'Instagram Follower Counter'),
('sv', 'us', 'global', 'hlink17', 'Buy TikTok Followers'),
('sv', 'us', 'global', 'hhref17', '/buy-tiktok-followers/'),
('sv', 'us', 'global', 'htitle17', 'Buy TikTok Followers'),
('sv', 'us', 'global', 'hlink18', 'Buy TikTok Likes'),
('sv', 'us', 'global', 'hhref18', '/buy-tiktok-likes/'),
('sv', 'us', 'global', 'htitle18', 'Buy TikTok Likes'),
('sv', 'us', 'global', 'hlink19', 'Buy TikTok Views'),
('sv', 'us', 'global', 'hhref19', '/buy-tiktok-views/'),
('sv', 'us', 'global', 'htitle19', 'Buy TikTok Views'),
('sv', 'us', 'global', 'hlink20', 'Free Instagram Likes'),
('sv', 'us', 'global', 'hhref20', '/free-packages/free-instagram-likes/'),
('sv', 'us', 'global', 'htitle20', 'Free Instagram Likes'),
('sv', 'us', 'global', 'hlink21', 'Free Instagram Followers'),
('sv', 'us', 'global', 'hhref21', '/free-packages/free-instagram-followers/'),
('sv', 'us', 'global', 'htitle21', 'Free Instagram Followers'),
('sv', 'us', 'global', 'hlink22', 'Free TikTok Likes'),
('sv', 'us', 'global', 'hhref22', '/free-packages/free-tiktok-likes/'),
('sv', 'us', 'global', 'htitle22', 'Free TikTok Likes'),
('sv', 'us', 'global', 'hlink23', 'Free TikTok Followers'),
('sv', 'us', 'global', 'hhref23', '/free-packages/free-tiktok-followers/'),
('sv', 'us', 'global', 'htitle23', 'Free TikTok Followers');

-- UK content
INSERT INTO `content` (`brand`, `country`, `page`, `name`, `content`) VALUES
('sv', 'uk', 'home', 'metadesc', 'Buy Instagram followers, likes and views from Superviral UK.'),
('sv', 'uk', 'home', 'title', 'Buy Instagram Followers UK | Superviral'),
('sv', 'uk', 'home', 'canonical', 'https://superviral.io/uk/'),
('sv', 'uk', 'global', 'footercopyright', '© 2024 Superviral. All rights reserved.');

-- US footer copyright
INSERT INTO `content` (`brand`, `country`, `page`, `name`, `content`) VALUES
('sv', 'us', 'global', 'footercopyright', '© 2024 Superviral. All rights reserved.');

SELECT 'Database initialized successfully!' as status;
