#! /usr/bin/env python

import MySQLdb
import time

from utils import *

"""
ALTER TABLE  `meneame`.`users` ADD INDEX (  `user_url` );

ALTER TABLE  `blogs` ADD  `blog_feed` CHAR( 128 ) NULL DEFAULT NULL AFTER  `blog_url` ,
ADD  `blog_feed_checked` TIMESTAMP NULL AFTER  `blog_feed`,
ADD  `blog_feed_read` TIMESTAMP NULL AFTER  `blog_feed_checked`;

CREATE TABLE  `meneame`.`rss` (
`blog_id` INT UNSIGNED NOT NULL ,
`user_id` INT UNSIGNED NOT NULL DEFAULT  '0',
`link_id` INT UNSIGNED NULL ,
`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`date_parsed` TIMESTAMP NULL ,
`url` CHAR( 250 ) NOT NULL ,
`title` CHAR( 250 ) NOT NULL
) ENGINE = INNODB;

ALTER TABLE  `meneame`.`rss` ADD INDEX (  `date` );
ALTER TABLE  `meneame`.`rss` ADD INDEX (  `blog_id` ,  `date` );
ALTER TABLE  `meneame`.`rss` ADD INDEX (  `user_id` ,  `date` );
ALTER TABLE  `meneame`.`rss` ADD UNIQUE ( `url` );
"""

def main():
	blogs = get_candidate_blogs(120, 6.5)
	for id in blogs:
		print blogs[id]
		read_feed(id, blogs[id])

def get_candidate_blogs(days, min_karma):
	now = time.time();
	cursor = DBM.cursor()
	cursor.execute ("SELECT link_blog, blog_url, blog_feed, UNIX_TIMESTAMP(blog_feed_checked), UNIX_TIMESTAMP(blog_feed_read), count(*) as n  from links, blogs where link_status in ('published') and link_date > date_sub(now(), interval %s day) and blog_id = link_blog and blog_type='blog' group by blog_id", (days,))
	blogs= {}
	for row in cursor:
		blog_id, blog_url, blog_feed, blog_checked, blog_feed_read, counter = row
		base_url = blog_url.replace('http://', '').replace('www.', '')
		if counter < days and not is_site_banned(base_url):
			c = DBM.cursor()
			c.execute("select user_login, user_id from users where user_url in (%s, %s, %s, %s, %s, %s) and user_karma > %s order by user_karma desc limit 1",
					('http://'+base_url, 'http://www.'+base_url, 'http://'+base_url+'/', 'http://www.'+base_url+'/', base_url, 'www.'+base_url, min_karma))
			r = c.fetchone()
			c.close()
			if r is not None:
				if not blog_feed and (not blog_checked or blog_checked < now - 86400):
					blog_feed = get_feed_url(blog_url, blog_id)

				if blog_feed and (not blog_feed_read or blog_feed_read < now - 3600):
					blogs[blog_id] = {"url":blog_url, "feed":blog_feed, "user": r[0], "user_id": r[1], "read": blog_feed_read}
					#print "Added ", blog_id, blogs[blog_id]
	cursor.close ()
	DBM.close()
	return blogs

if __name__ == "__main__":
	main()
