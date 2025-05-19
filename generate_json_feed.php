<?php

// Define the output file path
$outputFile = __DIR__ . '/public/feeds/scraped/ai_news_feed.json';

// Define the feed data structure
$feedData = [
  "title" => "RSS Feed - www.artificialintelligence-news.com",
  "link" => "https://www.artificialintelligence-news.com/",
  "description" => "Generated RSS feed from https://www.artificialintelligence-news.com/",
  "language" => "vi",
  "lastBuildDate" => date('D, d M Y H:i:s O'),
  "items" => [
    [
      "title" => "The role of machine learning in enhancing cloud-native container security",
      "link" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Innovative machine learning uses transforming business applications",
      "link" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "AI and bots allegedly used to fraudulently boost music streams",
      "link" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Best data security platforms of 2025",
      "link" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "AI tool speeds up government feedback, experts urge caution",
      "link" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Alibaba Wan2.1-VACE: Open-source AI video tool for all",
      "link" => "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Apple developing custom chips for smart glasses and more",
      "link" => "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Will the AI boom fuel a global energy crisis?",
      "link" => "https://www.artificialintelligence-news.com/news/will-the-ai-boom-fuel-a-global-energy-crisis/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/will-the-ai-boom-fuel-a-global-energy-crisis/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Can the US really enforce a global AI chip ban?",
      "link" => "https://www.artificialintelligence-news.com/news/can-the-us-really-enforce-a-global-ai-chip-ban/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/can-the-us-really-enforce-a-global-ai-chip-ban/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Congress pushes GPS tracking for every exported semiconductor",
      "link" => "https://www.artificialintelligence-news.com/news/congress-pushes-gps-tracking-for-every-exported-semiconductor/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/congress-pushes-gps-tracking-for-every-exported-semiconductor/",
      "pubDate" => date('D, d M Y H:i:s O')
    ]
  ]
];

// Ensure the directory exists
if (!file_exists(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0755, true);
}

// Save the feed data to a JSON file
file_put_contents($outputFile, json_encode($feedData, JSON_PRETTY_PRINT));

echo "AI news feed generated successfully at " . date('Y-m-d H:i:s') . "\n";
echo "Output saved to {$outputFile}\n";
