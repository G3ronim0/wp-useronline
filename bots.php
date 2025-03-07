<?php

function useronline_get_bots() {
	$bots = array(
		'360Spider' => '360spider',
		'AddThis' => 'addthis',
		'Ahrefs' => 'ahrefsbot',
		'Alex' => 'ia_archiver',
		'AllTheWeb' => 'fast-webcrawler',
		'Altavista' => 'scooter',
		'Amazon' => 'amazonaws.com',
		'Anders Pink' => 'anderspinkbot',
		'Apple' => 'applebot',
		'Archive.org' => 'archive.org_bot',
		'Ask Jeeves' => 'jeeves',
		'Baidu' => 'baidu',
		'Become.com' => 'become.com',
		'Bing' => 'bingbot',
		'Bing Preview' => 'bingpreview',
		'BLEXBot' => 'blexbot',
		'Bloglines' => 'bloglines',
		'Blog Search Engine' => 'blogsearch',
		'BUbiNG' => 'bubing',
		'CCBot' => 'ccbot',
		'CFNetwork' => 'cfnetwork',
		'Cliqzbot' => 'cliqzbot',
		'Crawl' => 'crawl',
		'Curl' => 'Curl',
		'DotBot' => 'dotbot',
		'DuckDuckGo' => 'duckduckbot',
		'EveryoneSocialBot' => 'everyonesocialbot',
		'Exalead' => 'exabot',
		'Facebook' => 'facebook',
		'Facebook Preview' => 'facebookexternalhit',
		'faceBot' => 'facebot',
		'Feedfetcher' => 'Feedfetcher',
		'Findexa' => 'findexa',
		'Flipboard Preview' => 'FlipboardProxy',
		'Gais' => 'gaisbo',
		'Gigabot' => 'gigabot',
		'Gluten Free' => 'gluten free crawler',
		'Google' => 'google',
		'Grid' => 'gridbot',
		'GroupHigh' => 'grouphigh',
		'Heritrix' => 'heritrix',
		'IA Archiver' => 'ia_archiver',
		'Inktomi' => 'slurp@inktomi',
		'IPS Agent' => 'ips-agent',
		'James' => 'james bot',
		'KomodiaBot' => 'komodiabot',
		'Konqueror' => 'konqueror',
		'Lindex' => 'linkdexbot',
		'Linkfluence' => 'linkfluence',
		'Lycos' => 'lycos',
		'Maui' => 'mauibot',
		'Mediatoolkit' => 'mediatoolkitbot',
		'MetaURI' => 'metauri',
		'MJ12bot' => 'mj12bot',
		'MojeekBot' => 'mojeekBot',
		'Moreover' => 'moreover',
		'MSN' => 'msnbot',
		'NBot' => 'nbot',
		'oBot' => 'oBot',
		'NextLinks' => 'findlinks',
		'PaperLiBot' => 'paperliBot',
		'PhantomJS' => 'phantomjs',
		'Proximic' => 'proximic',
		'PubSub' => 'pubsub',
		'Qwantify' => 'qwantify',
		'Radian6' => 'radian6',
		'RadioUserland' => 'userland',
		'Moz' => 'rogerbot',
		'SEOkicks' => 'seokicks-robot',
		'SemrushBot' => 'semrushbot',
		'Seznam' => 'seznam',
		'SiteExplorer' => 'siteexplorer',
		'Slurp' => 'slurp',
		'Sogou' => 'Sogou',
		'OpenLinkProfiler.org' => 'spbot',
		'SurveyBot' => 'surveybot',
		'Syndic8' => 'syndic8',
		'Technorati' => 'technorati',
		'TelegramBot' => 'telegrambot',
		'TraceMyFile' => 'tracemyfile',
		'Trendsmap' => 'trendsmap',
		'Turnitin.com' => 'turnitinbot',
		'The Tweeted Times' => 'tweetedtimes',
		'TweetmemeBot' => 'tweetmemeBot',
		'Twingly' => 'twingly',
		'Twitter' => 'twitterbot',
		'Wget' => 'wget',
		'WhatsApp' => 'whatsapp',
		'WhoisSource' => 'surveybot',
		'WiseNut' => 'zyborg',
		'Xenu Link Sleuth' => 'xenu link sleuth',
		'XoviBot' => 'xoviBot',
		'Yahoo' => 'yahoo',
		'Yandex' => 'yandex',
		'YisouSpider' => 'yisouspider'
	);

	return apply_filters( 'useronline_bots', $bots );
}

