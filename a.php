<?php
error_reporting(E_ALL); // Show all errors for debugging

print($aku);
date_default_timezone_set("Asia/JAKARTA");

$cookie = file_get_contents('cookies.txt'); //Cookie Kamu

$csrf_token = preg_match_all('/ct0=(.*?);/', $cookie, $csrf_token) ? $csrf_token[1][0] : null;
$target = '369651328';

$getTweetAtHome = 'https://x.com/i/api/graphql/VgitpdpNZ-RUIp5D1Z_D-A/UserTweets?variables=%7B%22userId%22%3A%22' . $target . '%22%2C%22count%22%3A20%2C%22includePromotedContent%22%3Afalse%2C%22withQuickPromoteEligibilityTweetFields%22%3Afalse%2C%22withVoice%22%3Atrue%2C%22withV2Timeline%22%3Atrue%7D&features=%7B%22responsive_web_graphql_exclude_directive_enabled%22%3Atrue%2C%22verified_phone_label_enabled%22%3Afalse%2C%22responsive_web_home_pinned_timelines_enabled%22%3Atrue%2C%22creator_subscriptions_tweet_preview_api_enabled%22%3Atrue%2C%22responsive_web_graphql_timeline_navigation_enabled%22%3Atrue%2C%22responsive_web_graphql_skip_user_profile_image_extensions_enabled%22%3Afalse%2C%22c9s_tweet_anatomy_moderator_badge_enabled%22%3Atrue%2C%22tweetypie_unmention_optimization_enabled%22%3Atrue%2C%22responsive_web_edit_tweet_api_enabled%22%3Atrue%2C%22graphql_is_translatable_rweb_tweet_is_translatable_enabled%22%3Atrue%2C%22view_counts_everywhere_api_enabled%22%3Atrue%2C%22longform_notetweets_consumption_enabled%22%3Atrue%2C%22responsive_web_twitter_article_tweet_consumption_enabled%22%3Afalse%2C%22tweet_awards_web_tipping_enabled%22%3Afalse%2C%22freedom_of_speech_not_reach_fetch_enabled%22%3Atrue%2C%22standardized_nudges_misinfo%22%3Atrue%2C%22tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled%22%3Atrue%2C%22longform_notetweets_rich_text_read_enabled%22%3Atrue%2C%22longform_notetweets_inline_media_enabled%22%3Atrue%2C%22responsive_web_media_download_video_enabled%22%3Afalse%2C%22responsive_web_enhance_cards_enabled%22%3Afalse%7D';

$headersHome = array(
    'Content-Type: application/json;charset=utf-8',
    'accept-encoding: UTF-8',
    'accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
    'authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA',
    'Cookie: ' . $cookie,
    'referer: https://x.com/LazadaID',
    'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
    'sec-ch-ua-platform: "Android"',
    'user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Mobile Safari/537.36',
    'x-csrf-token: a86c003119e18039081a28304dfdfd9405d59a916661910908060f31eae91f69d17a132dd40e14d50963c4fcaa0b7bdfb925068d28823ff5892e2fffaa51d02c357181a8f9356c704556985e52673294',
    'x-twitter-auth-type: OAuth2Session',
    'x-twitter-client-language: en'
);

$postHome = getUpdates($getTweetAtHome, false, $cookie, $headersHome);
$dataTwitter = json_decode($postHome["content"])->data->user->result->timeline_v2->timeline->instructions;

foreach ($dataTwitter as $instruction) {
    if ($instruction->type === "TimelineAddEntries") {
        $entries = $instruction->entries;

        foreach ($entries as $entry) {
            if (isset($entry->content->itemContent->tweet_results->result->legacy->id_str)) {
                $tweetID = $entry->content->itemContent->tweet_results->result->legacy->id_str;
                $tweetText = $entry->content->itemContent->tweet_results->result->legacy->full_text;
                $retweetCount = $entry->content->itemContent->tweet_results->result->legacy->retweet_count;
                $replyCount = $entry->content->itemContent->tweet_results->result->legacy->reply_count;

                echo "TweetID => $tweetID\n";
                echo "Isi Pesan => $tweetText\n";
                echo "Jumlah Retweet => $retweetCount\n";
                echo "Jumlah Reply => $replyCount\n";
                echo "\n";
                // Simpan ID tweet ke dalam logs.txt jika belum ada
                if (!in_array($tweetID, readExistingTweetIds())) {
                    file_put_contents('logs.txt', $tweetID . PHP_EOL, FILE_APPEND);
                }
            }
        }
    }
}

$headers = array();
$headers[] = "Accept-Encoding: UTF-8";
$headers[] = "Content-Type: application/json";
$headers[] = "x-csrf-token: $csrf_token";
$headers[] = "authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA";
$headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0";
$headers[] = "Cookie: $cookie";
$post_data = [
    "variables" => [
        "dark_request" => false,
    ]
];

if (file_exists('logs_tweet.txt')) {
    $log = file_get_contents('logs_tweet.txt');
} else {
    $log = '';
}

foreach ($entries as $entry) {
    if (isset($entry->content->itemContent->tweet_results->result->legacy->id_str)) {
        $tweetId = $entry->content->itemContent->tweet_results->result->legacy->id_str;
        
        if (!preg_match("/" . $tweetId . "/", $log)) {
            echo " => Eksekusi";
            $x = $tweetId . "\n";
            file_put_contents('logs_tweet.txt', $x, FILE_APPEND);
            
            $post = cURL("https://x.com/i/api/graphql/VaenaVgh5q5ih7kvyVjgtg/DeleteTweet", $headers, json_encode($post_data));

            if (isset($post)) {
                echo " [delete Success]<br>";
            } else {
                echo " [delete Gagal]<br>";
            }
        } else {
            echo " [OKE!]<br>";
        }
    }
}

function getUpdates($url, $fields = null, $cookie = null, $httpheader = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.52 Safari/537.36');
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    if ($fields) {
        $field_string = http_build_query($fields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $body = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);
    return ['errno' => $err, 'errmsg' => $errmsg, 'content' => $body];
}

function cURL($url, $header = null, $postfields = null, $useragent = null, $cookie = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($postfields !== false) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
    
    if ($cookie !== false) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    }
    if ($header !== false) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function readExistingTweetIds()
{
    $tweetIds = [];
    if (file_exists('logs.txt')) {
        $tweetIds = file('logs.txt', FILE_IGNORE_NEW_LINES);
    }
    return $tweetIds;
}
