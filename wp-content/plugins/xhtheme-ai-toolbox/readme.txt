=== XHTheme AI Toolbox ===
Contributors: xhtheme
Donate link: https://www.paypal.com/paypalme/xhtheme
Tags: ai, auto comments, auto tags, ai summary, XHTheme AI Toolbox
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.0
Stable tag: 1.8.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI tag extraction, AI image, AI summary, comment generation, AI topic expansion, auto-classification, slug generation and AI content enhancement.

== Description ==

XHTHheme AI Toolbox is a powerful WordPress plugin designed for content creators, integrating advanced AI language models including DeepSeek and Qwen3. This plugin supports content generation, paragraph optimization, automatic commenting, article topic expansion, AI text-to-image (automatically generating illustrations and covers), tag extraction, summary generation, automatic aliasing and category assignment, significantly improving content creation efficiency and quality.

The plugin supports various article types including image-only, text-only, and mixed image-text content, but does not support video recognition.

[Official Overview](https://www.xhtheme.com/xhtheme-ai-toolbox)

== Key Features ==

= Intelligent Image Recognition =
When article text content is too short or for image-only articles, content is compensated through image recognition, improving the quality and accuracy of generated content.

= AI Automatic Image Generation =
Generate images for articles and set them as covers, insert images into article content (optional) through Tongyi Wanxiang text-to-image interface. Text-to-image generation requires connecting your own Alibaba Cloud Bailian Platform API-KEY. New users can enjoy 1000 free credits (500 images for each of the two models)!

= AI Auto Comments =
Analyzes article topics, viewpoints, and writing style through AI to generate relevant or interactive simulated comments and schedule their publication.

= AI Topic Expansion =
Automatically analyzes article content through AI, extracts reading directions that can be extended to generate topics and content, and displays topic tags below the main text. This feature can enrich content matrix and improve creation efficiency.

[View Demo Page](https://meteor.demo.cxory.com/information/333.html)

= AI Tag Extraction =
Intelligently analyzes article content through AI to automatically extract the most relevant TAG labels and automatically set SEO titles, English aliases, tag descriptions and other fields for new tags.

= AI Summary Generation =
Intelligently generates AI summaries and displays them above the article body, enhancing the user's reading experience. The plugin offers multiple preset styles and supports using AI capabilities to generate custom styles. It can produce traditional article summaries and engaging guidance-type summaries as needed, making readers more interested in the article content.

= AI Content Generation/Optimization =
Uses AI models to generate or optimize paragraph content, improving article editing efficiency. If you're not satisfied with the content, you can undo and switch models to generate new content.

= AI Intelligent Categorization =
Intelligently analyzes article content through AI and selects the most appropriate category from existing categories.

= Automatic English Alias Generation =
Extracts content-related English aliases through AI for URLs, intelligently matching keywords to give URLs more depth.

= Usage Limitations =
All users get 100 free AI model requests per month. Beyond that, you need to purchase resource packages from the official website!

== How to Use? ==

= 1. Trigger AI capabilities through the AI panel: =  
- Supports specifying which AI capabilities and custom models to use
- Supports use in the block editor (Gutenberg)
- Supports all features and allows applying data after secondary modifications

= 2. Use AI capabilities through the automated queue: = 
- When publishing articles, you can control whether to join the queue, with backend default settings available
- After joining the queue, tasks run in the background, improving efficiency without requiring additional operations
- Supports all AI capabilities
- Supports use in both classic editor and block editor (Gutenberg)

= 3. Apply AI capabilities through list shortcut buttons: = 
- We've integrated AI running status display and operable AI shortcut buttons in the backend article list
- Quickly apply individual AI capabilities through shortcut buttons
- Supports use in both classic editor and block editor (Gutenberg)

= 4. Apply AI capabilities through parameters: = 
- This feature is suitable for article publishing programs or collection publishing programs
- When publishing articles, carrying PostMeta queue parameters automatically adds AI tasks to the pending execution queue

[View Documentation](https://www.xhtheme.com/docs/aitoolbox)

== Screenshots ==

1. AI completion feature panel
2. Plugin settings page
3. AI summary generation feature
4. AI Tag Extraction and Metadata Auto-Completion Settings
5. Automatic comment settings

== Installation ==

1. Upload the `xhtheme-ai-toolbox` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin panel
3. Go to "Settings > XHTheme AI Toolbox" to configure options
4. Start using AI features to enhance your content workflow

== Frequently Asked Questions ==

= Does the plugin require an API key? =

Yes, an API key is required as it needs to call AI model interfaces for content generation.

= Does the plugin support multilingual? =

Yes, the plugin supports multiple languages with good compatibility for English and Chinese.

= What external services does the plugin connect to? =

The plugin needs to connect to XHTheme AI API (https://www.xhtheme.com) to implement AI content optimization functions. The API is used for unified calling and allocation of large language models, applicable to paragraph optimization, summary generation, tag extraction, topic generation, comment generation, intelligent categorization, automatic alias, and other related functions.
The platform only performs model interface routing and authentication, and does not store user data or generated data. In special cases, log information is kept for no more than 30 days.
Terms of Service: https://www.xhtheme.com/agreement/
Privacy Policy: https://www.xhtheme.com/privacy-policy/
To use this service, you need to obtain an API key (APPID), which can be applied for through the XHTheme official website.

== Changelog ==

= 1.8.3 =
Cancel REST API detection popup

= 1.8.2 =
* Add new site configuration wizard
* Add REST API health check warning
* Update Alibaba Cloud image generation model endpoint
* Add queue task timeout handling
* Add GPT-5 model support
* Add queue task count display in menu
* Update default values for some settings
* Comment avatar enhances compatibility with certain themes

= 1.8.1 =
* Improve summary settings compatibility for Zibll theme

= 1.8 =
* Added topic list page (enable in plugin settings)
* Added modern style template for topic list page
* Added aspect ratio setting for topic image generation
* Added personalized settings options for topic generation
* Optimized frontend code loading logic
* Optimized queue execution mechanism
* Optimized scheduled task processing
* Optimized admin list styles
* Fixed an issue where AI summary could not be removed from default excerpt in some cases

= 1.7.4 =
* Optimized scheduled task processing scheme

= 1.7.3 =
* Fixed an issue where task queue caused scheduled task backlog in some cases

= 1.7.2 =
* Optimized queue execution logic to improve efficiency

= 1.7.1 =
* Fixed Zibll theme avatar retrieval
* Optimized queue execution logic to improve efficiency

= 1.7 =
* Refactored the automation module, switching automation to a rule-based mode
* Enhanced queue functionality, adding features such as batch delete/retry
* Optimized queue execution logic and added a manual trigger button
* Added an option for AI Summary to push content to the default post excerpt
* Optimized AI summary style loading logic for compatibility with themes that load post content via AJAX
* Added options for comments: Guest Comments, Registered User Comments, and Mixed Mode
* Added compatibility for B2 Theme's comment avatars and AI capability application for its "Shopping District" model
* Added comment avatar compatibility for Zibll Theme (custom avatars can be displayed for registered user comments)
* Improved general compatibility to work with a wider range of themes
* Added Zhipu and Official Channel options for the text-to-image API
* Added a "Content Image Only" option
* Fixed several style bugs and known issues
* Preparing for Phase 2 of development, which will focus on content and expanding more AI capabilities

= 1.6.3 =
* Updated the Tongyi Wanxiang (Alibaba text-to-image) API model to Wanxiang 2.2

= 1.6.2 =
* Optimize initialization functions to enhance compatibility

= 1.6.1 =
* Optimized configuration panel
* Fixed paragraph optimization bug

= 1.6 =
* Optimized language recognition logic to improve accuracy
* Enhanced paragraph optimization functionality, added translation and multiple rewriting options
* Added an engaging guidance mode option for AI summaries, focusing on guiding users to read article content
* Added support for inference models, paragraph optimization and AI panel now support displaying the inference process
* Added image recognition capability to compensate content when there is insufficient content or articles with only images
* Added AI image compression functionality, with cloud-based compression API for higher efficiency
* Optimized prompt algorithms
* Automated tasks now support exclusion by category disable function
* Added quota balance display module in the user backend


= 1.5.3 =
* Optimized excerpt control

= 1.5.2 =
* Optimized thread feature style

= 1.5.1 =
* Optimized thread feature style

= 1.5 =
* Added custom content multilingual support
* Restructured automated task control to support separate model control
* Restructured AI comment feature
* Restructured AI tag extraction processing logic
* Added adaptation for some themes
* Fixed known bugs
* Adapted to cloud API 2.0 version, quality and performance improved
* Added reminder popups for certain scenarios
* Added template options for topic feature

= 1.4.1 =
* Optimizing queue time zone unification issue

= 1.4 =
* Added support for WooCommerce products, enabling intelligent categorization, tag extraction, and review generation
* Implemented content language specification for precise returns; uses model auto-detection when unspecified (recommended for single-language sites)
* Optimized logic for topic quick-action buttons
* Added adaptation documentation module in admin panel

= 1.3 =
* Added text-to-image feature, new users of Alibaba Bailian can generate 1000 images for free
* Text-to-image supports automatic cover setting and inserting images into article content (optional)
* Optimized AI panel with additional detailed control options
* Enhanced queue functionality, improving the execution efficiency of chained tasks
* Added retry button for tasks
* Optimized interface calls and allocation
* Fixed frontend style bugs in the topic feature
* Added AI comment option to the topic feature
* Added automatic image generation option to the topic feature
* Added status display to the comment list

= 1.2 =
* Adapted for Classic Editor
* Added quick AI actions in the list
* Added queue feature, AI tasks executed in the background
* Added automation feature, automatically adding AI tasks when publishing posts
* Fixed compatibility with some themes

= 1.1 =
* Added: Unified modal component to replace native alert prompts
* Added: AI comment feature with background generation and randomized scheduling
* Added: AI-generated post slug feature
* Added: AI auto-category selection
* Improved: AppID validation with length check (15-30 characters)
* Fixed: Empty AppID handling during settings save
* Performance: Optimized API request handling
* Performance: Improved model list update mechanism

= 1.0 =
* Initial version release
* AI content generation/optimization
* AI auto comments
* AI tag extraction
* AI summary generation
* Multiple AI model integration