<?php

// Define the custom feed data structure exactly as provided by the user
$feedData = json_decode('{
  "title": "RSS Feed - www.artificialintelligence-news.com",
  "link": "https://www.artificialintelligence-news.com/",
  "description": "Generated RSS feed from https://www.artificialintelligence-news.com/",
  "language": "vi",
  "lastBuildDate": "Mon, 19 May 2025 01:08:41 +0000",
  "items": [
    {
      "title": "The role of machine learning in enhancing cloud-native container security",
      "link": "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "Innovative machine learning uses transforming business applications",
      "link": "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "AI and bots allegedly used to fraudulently boost music streams",
      "link": "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "Best data security platforms of 2025",
      "link": "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "AI tool speeds up government feedback, experts urge caution",
      "link": "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "Alibaba Wan2.1-VACE: Open-source AI video tool for all",
      "link": "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    },
    {
      "title": "Apple developing custom chips for smart glasses and more",
      "link": "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
      "description": "No description available.",
      "guid": "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
      "pubDate": "Mon, 19 May 2025 01:08:41 +0000"
    }
  ]
}', true);

// Set content type header to JSON
header('Content-Type: application/json');

// Output the JSON data
echo json_encode($feedData, JSON_PRETTY_PRINT);
