{\rtf1\ansi\ansicpg1252\cocoartf2862
\cocoatextscaling0\cocoaplatform0{\fonttbl\f0\fnil\fcharset0 Menlo-Bold;\f1\fnil\fcharset0 Menlo-Regular;}
{\colortbl;\red255\green255\blue255;\red211\green83\blue92;\red23\green24\blue24;\red202\green202\blue202;
\red109\green115\blue120;\red183\green111\blue247;\red212\green212\blue212;\red113\green192\blue131;\red246\green124\blue48;
\red99\green159\blue215;\red109\green109\blue109;\red70\green137\blue204;\red140\green211\blue254;\red194\green126\blue101;
\red56\green78\blue153;}
{\*\expandedcolortbl;;\cssrgb\c86667\c41569\c43529;\cssrgb\c11765\c12157\c12549;\cssrgb\c83137\c83137\c83137;
\cssrgb\c50196\c52549\c54510;\cssrgb\c77255\c54118\c97647;\cssrgb\c86275\c86275\c86275;\cssrgb\c50588\c78824\c58431;\cssrgb\c98039\c56471\c24314;
\cssrgb\c45490\c69020\c87451;\cssrgb\c50196\c50196\c50196;\cssrgb\c33725\c61176\c83922;\cssrgb\c61176\c86275\c99608;\cssrgb\c80784\c56863\c47059;
\cssrgb\c28235\c39216\c66667;}
\margl1440\margr1440\vieww11520\viewh8400\viewkind0
\deftab720
\pard\pardeftab720\partightenfactor0

\f0\b\fs28 \cf2 \cb3 \expnd0\expndtw0\kerning0
\outl0\strokewidth0 \strokec2 <?php
\f1\b0 \cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 /**\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * Plugin Name:       AI SEO Writer\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * Description:       Generate and enhance SEO-friendly articles using a full suite of AI tools.\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * Version:           2.0.0\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * Author:            Your Name\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * License:           GPL-2.0-or-later\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * License URI:       https://www.gnu.org/licenses/gpl-2.0.html\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  * Text Domain:       ai-seo-writer\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5  */\cf4 \cb1 \strokec4 \
\
\cf5 \cb3 \strokec5 // Prevent direct file access\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  \cf7 \strokec7 !\cf4 \strokec4  defined\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'ABSPATH'\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     \cf6 \strokec6 exit\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // --- 1. Admin Menu & Settings Setup ---\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5 // This section remains largely the same, setting up our menu pages.\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_add_admin_menu\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     add_menu_page\cf7 \strokec7 (\cf8 \strokec8 'AI SEO Writer'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'AI Writer'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'manage_options'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_main_page_html'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'dashicons-edit-large'\cf7 \strokec7 ,\cf4 \strokec4  \cf9 \strokec9 6\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     add_submenu_page\cf7 \strokec7 (\cf8 \strokec8 'ai_seo_writer'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'AI Writer Settings'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'Settings'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'manage_options'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_settings_page_html'\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3 add_action\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'admin_menu'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_add_admin_menu'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_settings_init\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     register_setting\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings_group'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_openai_api_key'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     add_settings_section\cf7 \strokec7 (\cf8 \strokec8 'aisw_settings_section'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'OpenAI API Settings'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_settings_section_callback'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings'\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     add_settings_field\cf7 \strokec7 (\cf8 \strokec8 'aisw_openai_api_key_field'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'OpenAI API Key'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_api_key_field_callback'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_settings_section'\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3 add_action\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'admin_init'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_settings_init'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_settings_section_callback\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     \cf6 \strokec6 echo\cf4 \strokec4  \cf8 \strokec8 '<p>Enter your OpenAI API key below. This is required to generate content.</p>'\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_api_key_field_callback\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     \cf10 \strokec10 $api_key\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  get_option\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw_openai_api_key'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf6 \strokec6 echo\cf4 \strokec4  \cf8 \strokec8 '<input type="password" name="aisw_openai_api_key" value="'\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  esc_attr\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $api_key\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf8 \strokec8 '" class="regular-text">'\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // --- 2. Main Page HTML Structure ---\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5 // This has been significantly updated with the new controls and the hidden "Tune-Up" panel.\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_main_page_html\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     
\f0\b \cf2 \strokec2 ?>
\f1\b0 \cf4 \cb1 \strokec4 \
\cb3     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "wrap aisw-wrap"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-header"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 <\cf12 \strokec12 h1\cf11 \strokec11 >
\f0\b \cf2 \strokec2 <?php
\f1\b0 \cf4 \strokec4  \cf6 \strokec6 echo\cf4 \strokec4  esc_html\cf7 \strokec7 (\cf4 \strokec4  get_admin_page_title\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 );\cf4 \strokec4  
\f0\b \cf2 \strokec2 ?>
\f1\b0 \cf11 \strokec11 </\cf12 \strokec12 h1\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 <\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \strokec4 Define the new reality. Enter a topic and generate the narrative.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cb1 \
\cb3         \cf5 \strokec5 <!-- Step 1: Generator Form -->\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "aisw-generator-app"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "aisw-step-1-generator"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "form-group"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 label\cf4 \strokec4  \cf13 \strokec13 for\cf7 \strokec7 =\cf14 \strokec14 "article-topic"\cf11 \strokec11 >\cf4 \strokec4 Article Topic\cf11 \strokec11 </\cf12 \strokec12 label\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 input\cf4 \strokec4  \cf13 \strokec13 type\cf7 \strokec7 =\cf14 \strokec14 "text"\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "article-topic"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "large-text"\cf4 \strokec4  \cf13 \strokec13 placeholder\cf7 \strokec7 =\cf14 \strokec14 "e.g., The resurgence of vinyl in the digital age"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-controls-grid"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "form-group"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 label\cf4 \strokec4  \cf13 \strokec13 for\cf7 \strokec7 =\cf14 \strokec14 "article-tone"\cf11 \strokec11 >\cf4 \strokec4 Tone\cf11 \strokec11 </\cf12 \strokec12 label\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 select\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "article-tone"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                             \cf11 \strokec11 <\cf12 \strokec12 option\cf4 \strokec4  \cf13 \strokec13 value\cf7 \strokec7 =\cf14 \strokec14 "Professional"\cf11 \strokec11 >\cf4 \strokec4 Professional\cf11 \strokec11 </\cf12 \strokec12 option\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                             \cf11 \strokec11 <\cf12 \strokec12 option\cf4 \strokec4  \cf13 \strokec13 value\cf7 \strokec7 =\cf14 \strokec14 "Casual"\cf4 \strokec4  \cf13 \strokec13 selected\cf11 \strokec11 >\cf4 \strokec4 Casual\cf11 \strokec11 </\cf12 \strokec12 option\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                             \cf11 \strokec11 <\cf12 \strokec12 option\cf4 \strokec4  \cf13 \strokec13 value\cf7 \strokec7 =\cf14 \strokec14 "Witty"\cf11 \strokec11 >\cf4 \strokec4 Witty\cf11 \strokec11 </\cf12 \strokec12 option\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                             \cf11 \strokec11 <\cf12 \strokec12 option\cf4 \strokec4  \cf13 \strokec13 value\cf7 \strokec7 =\cf14 \strokec14 "Bold"\cf11 \strokec11 >\cf4 \strokec4 Bold\cf11 \strokec11 </\cf12 \strokec12 option\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                             \cf11 \strokec11 <\cf12 \strokec12 option\cf4 \strokec4  \cf13 \strokec13 value\cf7 \strokec7 =\cf14 \strokec14 "Technical"\cf11 \strokec11 >\cf4 \strokec4 Technical\cf11 \strokec11 </\cf12 \strokec12 option\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 </\cf12 \strokec12 select\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "form-group"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 label\cf4 \strokec4  \cf13 \strokec13 for\cf7 \strokec7 =\cf14 \strokec14 "article-audience"\cf11 \strokec11 >\cf4 \strokec4 Target Audience\cf11 \strokec11 </\cf12 \strokec12 label\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 input\cf4 \strokec4  \cf13 \strokec13 type\cf7 \strokec7 =\cf14 \strokec14 "text"\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "article-audience"\cf4 \strokec4  \cf13 \strokec13 placeholder\cf7 \strokec7 =\cf14 \strokec14 "e.g., Music lovers, audiophiles"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "generate-article-btn"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button button-primary"\cf11 \strokec11 >\cf4 \strokec4 Generate\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "aisw-live-progress"\cf4 \strokec4  \cf13 \strokec13 style\cf7 \strokec7 =\cf14 \strokec14 "display:none;"\cf11 \strokec11 ></\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\
\cb3             \cf5 \strokec5 <!-- Step 2: Tune-Up Panel (Initially Hidden) -->\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "aisw-step-2-tuneup"\cf4 \strokec4  \cf13 \strokec13 style\cf7 \strokec7 =\cf14 \strokec14 "display:none;"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 h2\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-tuneup-title"\cf11 \strokec11 >\cf4 \strokec4 Tijuana Tune-Up\cf11 \strokec11 </\cf12 \strokec12 h2\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 p\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-tuneup-subtitle"\cf11 \strokec11 >\cf4 \strokec4 Article draft created. Now, let's perfect it.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cb1 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-tuneup-grid"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf5 \strokec5 <!-- Meta Description -->\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "tuneup-module"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \strokec4 Meta Description\cf11 \strokec11 </\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \strokec4 Generate a concise, 160-character summary for search engines.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button tuneup-btn"\cf4 \strokec4  \cf13 \strokec13 data-action\cf7 \strokec7 =\cf14 \strokec14 "generate_meta"\cf11 \strokec11 >\cf4 \strokec4 Generate Meta\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 textarea\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "meta-output"\cf4 \strokec4  \cf13 \strokec13 readonly\cf11 \strokec11 ></\cf12 \strokec12 textarea\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf5 \strokec5 <!-- SEO Tags -->\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "tuneup-module"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \strokec4 SEO Tags\cf11 \strokec11 </\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \strokec4 Generate 5-7 relevant tags for your post.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button tuneup-btn"\cf4 \strokec4  \cf13 \strokec13 data-action\cf7 \strokec7 =\cf14 \strokec14 "generate_tags"\cf11 \strokec11 >\cf4 \strokec4 Suggest Tags\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "tags-output"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "output-box"\cf11 \strokec11 ></\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf5 \strokec5 <!-- Social Teaser -->\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "tuneup-module"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \strokec4 Social Teaser (X/Twitter)\cf11 \strokec11 </\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \strokec4 Generate a punchy tweet to promote this article.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button tuneup-btn"\cf4 \strokec4  \cf13 \strokec13 data-action\cf7 \strokec7 =\cf14 \strokec14 "generate_social"\cf11 \strokec11 >\cf4 \strokec4 Create Teaser\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 textarea\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "social-output"\cf4 \strokec4  \cf13 \strokec13 readonly\cf11 \strokec11 ></\cf12 \strokec12 textarea\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf5 \strokec5 <!-- Featured Image -->\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "tuneup-module"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \strokec4 Featured Image\cf11 \strokec11 </\cf12 \strokec12 h3\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \strokec4 Find a high-quality, royalty-free image for your post.\cf11 \strokec11 </\cf12 \strokec12 p\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button tuneup-btn"\cf4 \strokec4  \cf13 \strokec13 data-action\cf7 \strokec7 =\cf14 \strokec14 "find_image"\cf11 \strokec11 >\cf4 \strokec4 Find Image\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                         \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "image-output"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "output-box"\cf11 \strokec11 ></\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-finish-buttons"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 a\cf4 \strokec4  \cf13 \strokec13 href\cf7 \strokec7 =\cf14 \strokec14 "#"\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "edit-post-link"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button button-primary"\cf4 \strokec4  \cf13 \strokec13 target\cf7 \strokec7 =\cf14 \strokec14 "_blank"\cf11 \strokec11 >\cf4 \strokec4 Finish & Edit Post\cf11 \strokec11 </\cf12 \strokec12 a\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                     \cf11 \strokec11 <\cf12 \strokec12 button\cf4 \strokec4  \cf13 \strokec13 id\cf7 \strokec7 =\cf14 \strokec14 "start-over-btn"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "button button-secondary"\cf11 \strokec11 >\cf4 \strokec4 Start Over\cf11 \strokec11 </\cf12 \strokec12 button\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3                 \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3     
\f0\b \cf2 \strokec2 <?php
\f1\b0 \cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // Settings Page HTML (no changes needed here)\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_settings_page_html\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     
\f0\b \cf2 \strokec2 ?>
\f1\b0 \cf4 \cb1 \strokec4 \
\cb3     \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "wrap aisw-wrap"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 <\cf12 \strokec12 div\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-header"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             \cf11 \strokec11 <\cf12 \strokec12 h1\cf11 \strokec11 >
\f0\b \cf2 \strokec2 <?php
\f1\b0 \cf4 \strokec4  \cf6 \strokec6 echo\cf4 \strokec4  esc_html\cf7 \strokec7 (\cf4 \strokec4  get_admin_page_title\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 );\cf4 \strokec4  
\f0\b \cf2 \strokec2 ?>
\f1\b0 \cf11 \strokec11 </\cf12 \strokec12 h1\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 <\cf12 \strokec12 form\cf4 \strokec4  \cf13 \strokec13 action\cf7 \strokec7 =\cf14 \strokec14 "options.php"\cf4 \strokec4  \cf13 \strokec13 method\cf7 \strokec7 =\cf14 \strokec14 "post"\cf4 \strokec4  \cf13 \strokec13 class\cf7 \strokec7 =\cf14 \strokec14 "aisw-settings-form"\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3             
\f0\b \cf2 \strokec2 <?php
\f1\b0 \cf4 \cb1 \strokec4 \
\cb3             settings_fields\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings_group'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3             do_settings_sections\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'ai_seo_writer_settings'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3             submit_button\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'Save Key'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3             
\f0\b \cf2 \strokec2 ?>
\f1\b0 \cf4 \cb1 \strokec4 \
\cb3         \cf11 \strokec11 </\cf12 \strokec12 form\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3     \cf11 \strokec11 </\cf12 \strokec12 div\cf11 \strokec11 >\cf4 \cb1 \strokec4 \
\cb3     
\f0\b \cf2 \strokec2 <?php
\f1\b0 \cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // --- 3. Enqueue Scripts & Styles ---\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5 // No major changes, just ensuring it loads on the correct pages.\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_enqueue_admin_scripts\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $hook\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'toplevel_page_ai_seo_writer'\cf4 \strokec4  \cf7 \strokec7 !==\cf4 \strokec4  \cf10 \strokec10 $hook\cf4 \strokec4  \cf7 \strokec7 &&\cf4 \strokec4  \cf8 \strokec8 'ai-writer_page_ai_seo_writer_settings'\cf4 \strokec4  \cf7 \strokec7 !==\cf4 \strokec4  \cf10 \strokec10 $hook\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 return\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\cb3     wp_enqueue_style\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw-admin-css'\cf7 \strokec7 ,\cf4 \strokec4  plugin_dir_url\cf7 \strokec7 (\cf4 \strokec4  \cf12 \strokec12 __FILE__\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf8 \strokec8 'assets/admin.css'\cf7 \strokec7 ,\cf4 \strokec4  \cf7 \strokec7 [],\cf4 \strokec4  \cf8 \strokec8 '2.0.0'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     wp_enqueue_script\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw-admin-js'\cf7 \strokec7 ,\cf4 \strokec4  plugin_dir_url\cf7 \strokec7 (\cf4 \strokec4  \cf12 \strokec12 __FILE__\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf8 \strokec8 'assets/admin.js'\cf7 \strokec7 ,\cf4 \strokec4  \cf7 \strokec7 [\cf4 \strokec4  \cf8 \strokec8 'jquery'\cf4 \strokec4  \cf7 \strokec7 ],\cf4 \strokec4  \cf8 \strokec8 '2.0.0'\cf7 \strokec7 ,\cf4 \strokec4  \cf6 \strokec6 true\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     wp_localize_script\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw-admin-js'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_ajax_obj'\cf7 \strokec7 ,\cf4 \strokec4  \cf7 \strokec7 [\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'ajax_url'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  admin_url\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'admin-ajax.php'\cf4 \strokec4  \cf7 \strokec7 ),\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'nonce'\cf4 \strokec4     \cf7 \strokec7 =>\cf4 \strokec4  wp_create_nonce\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw_ajax_nonce'\cf4 \strokec4  \cf7 \strokec7 )\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3 add_action\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'admin_enqueue_scripts'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_enqueue_admin_scripts'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // --- 4. AJAX Handlers ---\cf4 \cb1 \strokec4 \
\cf5 \cb3 \strokec5 // This is where all the new backend logic lives.\cf4 \cb1 \strokec4 \
\
\cf5 \cb3 \strokec5 // Helper function to make API calls, reducing code repetition.\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_call_openai_api\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $prompt\cf7 \strokec7 ,\cf4 \strokec4  \cf10 \strokec10 $max_tokens\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf9 \strokec9 1500\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     \cf10 \strokec10 $api_key\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  get_option\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw_openai_api_key'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  \cf6 \strokec6 empty\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $api_key\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 return\cf4 \strokec4  \cf6 \strokec6 new\cf4 \strokec4  WP_Error\cf7 \strokec7 (\cf8 \strokec8 'api_key_missing'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'OpenAI API Key is not set.'\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $api_url\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 'https://api.openai.com/v1/chat/completions'\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $api_body\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf7 \strokec7 [\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'model'\cf4 \strokec4        \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'gpt-3.5-turbo'\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'messages'\cf4 \strokec4     \cf7 \strokec7 =>\cf4 \strokec4  \cf7 \strokec7 [[\cf8 \strokec8 'role'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'user'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'content'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf10 \strokec10 $prompt\cf7 \strokec7 ]],\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'temperature'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf9 \strokec9 0.7\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'max_tokens'\cf4 \strokec4   \cf7 \strokec7 =>\cf4 \strokec4  \cf10 \strokec10 $max_tokens\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 ];\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $response\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  wp_remote_post\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $api_url\cf7 \strokec7 ,\cf4 \strokec4  \cf7 \strokec7 [\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'headers'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf7 \strokec7 [\cf4 \cb1 \strokec4 \
\cb3             \cf8 \strokec8 'Authorization'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Bearer '\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf10 \strokec10 $api_key\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3             \cf8 \strokec8 'Content-Type'\cf4 \strokec4   \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'application/json'\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3         \cf7 \strokec7 ],\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'body'\cf4 \strokec4     \cf7 \strokec7 =>\cf4 \strokec4  json_encode\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $api_body\cf4 \strokec4  \cf7 \strokec7 ),\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'timeout'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf9 \strokec9 60\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  is_wp_error\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $response\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 return\cf4 \strokec4  \cf10 \strokec10 $response\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $response_body\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  wp_remote_retrieve_body\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $response\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $response_data\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  json_decode\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $response_body\cf7 \strokec7 ,\cf4 \strokec4  \cf6 \strokec6 true\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  \cf6 \strokec6 isset\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $response_data\cf7 \strokec7 [\cf8 \strokec8 'error'\cf7 \strokec7 ]\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 return\cf4 \strokec4  \cf6 \strokec6 new\cf4 \strokec4  WP_Error\cf7 \strokec7 (\cf8 \strokec8 'api_error'\cf7 \strokec7 ,\cf4 \strokec4  \cf10 \strokec10 $response_data\cf7 \strokec7 [\cf8 \strokec8 'error'\cf7 \strokec7 ][\cf8 \strokec8 'message'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 return\cf4 \strokec4  \cf10 \strokec10 $response_data\cf7 \strokec7 [\cf8 \strokec8 'choices'\cf7 \strokec7 ][\cf9 \strokec9 0\cf7 \strokec7 ][\cf8 \strokec8 'message'\cf7 \strokec7 ][\cf8 \strokec8 'content'\cf7 \strokec7 ];\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // Main article generation\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_handle_generate_article\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     check_ajax_referer\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw_ajax_nonce'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'nonce'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $topic\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  sanitize_text_field\cf7 \strokec7 (\cf15 \strokec15 $_POST\cf7 \strokec7 [\cf8 \strokec8 'topic'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $tone\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  sanitize_text_field\cf7 \strokec7 (\cf15 \strokec15 $_POST\cf7 \strokec7 [\cf8 \strokec8 'tone'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $audience\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  sanitize_text_field\cf7 \strokec7 (\cf15 \strokec15 $_POST\cf7 \strokec7 [\cf8 \strokec8 'audience'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 "You are an expert SEO content writer. Your task is to write a comprehensive, engaging, and SEO-optimized blog post on the topic of \\"\{$topic\}\\". The tone should be \{$tone\} and the target audience is \{$audience\}.\\n\\nThe article should have a compelling title, an introduction that hooks the reader, a well-structured body with H2 and H3 headings, and a concluding summary.\\n\\nPlease format the output as a valid JSON object with the following structure:\\n\{\\n  \\"title\\": \\"Your Generated Title\\",\\n  \\"body\\": \\"Your generated article content here, using HTML tags for formatting.\\"\\n\}"\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $ai_response_json\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  aisw_call_openai_api\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  is_wp_error\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $ai_response_json\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 (\cf4 \strokec4  \cf7 \strokec7 [\cf4 \strokec4  \cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'API Error: '\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf10 \strokec10 $ai_response_json\cf7 \strokec7 ->\cf4 \strokec4 get_error_message\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 ]\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $content\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  json_decode\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $ai_response_json\cf7 \strokec7 ,\cf4 \strokec4  \cf6 \strokec6 true\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  json_last_error\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 !==\cf4 \strokec4  JSON_ERROR_NONE \cf7 \strokec7 ||\cf4 \strokec4  \cf7 \strokec7 !\cf6 \strokec6 isset\cf7 \strokec7 (\cf10 \strokec10 $content\cf7 \strokec7 [\cf8 \strokec8 'title'\cf7 \strokec7 ])\cf4 \strokec4  \cf7 \strokec7 ||\cf4 \strokec4  \cf7 \strokec7 !\cf6 \strokec6 isset\cf7 \strokec7 (\cf10 \strokec10 $content\cf7 \strokec7 [\cf8 \strokec8 'body'\cf7 \strokec7 ])\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 (\cf4 \strokec4  \cf7 \strokec7 [\cf4 \strokec4  \cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Failed to parse AI response.'\cf4 \strokec4  \cf7 \strokec7 ]\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\cb3     \cb1 \
\cb3     \cf10 \strokec10 $post_id\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  wp_insert_post\cf7 \strokec7 ([\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'post_title'\cf4 \strokec4    \cf7 \strokec7 =>\cf4 \strokec4  sanitize_text_field\cf7 \strokec7 (\cf10 \strokec10 $content\cf7 \strokec7 [\cf8 \strokec8 'title'\cf7 \strokec7 ]),\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'post_content'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  wp_kses_post\cf7 \strokec7 (\cf10 \strokec10 $content\cf7 \strokec7 [\cf8 \strokec8 'body'\cf7 \strokec7 ]),\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'post_status'\cf4 \strokec4   \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'draft'\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3         \cf8 \strokec8 'post_author'\cf4 \strokec4   \cf7 \strokec7 =>\cf4 \strokec4  get_current_user_id\cf7 \strokec7 (),\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $post_id\cf4 \strokec4  \cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_success\cf7 \strokec7 ([\cf4 \strokec4  \cb1 \
\cb3             \cf8 \strokec8 'message'\cf4 \strokec4    \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Successfully created draft post!'\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3             \cf8 \strokec8 'post_id'\cf4 \strokec4    \cf7 \strokec7 =>\cf4 \strokec4  \cf10 \strokec10 $post_id\cf7 \strokec7 ,\cf4 \cb1 \strokec4 \
\cb3             \cf8 \strokec8 'edit_link'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  get_edit_post_link\cf7 \strokec7 (\cf4 \strokec4  \cf10 \strokec10 $post_id\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'raw'\cf4 \strokec4  \cf7 \strokec7 )\cf4 \cb1 \strokec4 \
\cb3         \cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \strokec4  \cf6 \strokec6 else\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 (\cf4 \strokec4  \cf7 \strokec7 [\cf4 \strokec4  \cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Failed to create WordPress post.'\cf4 \strokec4  \cf7 \strokec7 ]\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3 add_action\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'wp_ajax_aisw_generate_article'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_handle_generate_article'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\pard\pardeftab720\partightenfactor0
\cf5 \cb3 \strokec5 // Tune-Up: Generic handler for Meta, Tags, and Social\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf6 \cb3 \strokec6 function\cf4 \strokec4  aisw_handle_tuneup_actions\cf7 \strokec7 ()\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3     check_ajax_referer\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'aisw_ajax_nonce'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'nonce'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $post_id\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  intval\cf7 \strokec7 (\cf15 \strokec15 $_POST\cf7 \strokec7 [\cf8 \strokec8 'post_id'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $action\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  sanitize_text_field\cf7 \strokec7 (\cf15 \strokec15 $_POST\cf7 \strokec7 [\cf8 \strokec8 'tuneup_action'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $post\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  get_post\cf7 \strokec7 (\cf10 \strokec10 $post_id\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (!\cf10 \strokec10 $post\cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 ([\cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Post not found.'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $article_content\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 "Title: "\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf10 \strokec10 $post\cf7 \strokec7 ->\cf4 \strokec4 post_title \cf7 \strokec7 .\cf4 \strokec4  \cf8 \strokec8 "\\n\\nContent: "\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  wp_strip_all_tags\cf7 \strokec7 (\cf10 \strokec10 $post\cf7 \strokec7 ->\cf4 \strokec4 post_content\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 ''\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3     \cf10 \strokec10 $max_tokens\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf9 \strokec9 100\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 switch\cf4 \strokec4  \cf7 \strokec7 (\cf10 \strokec10 $action\cf7 \strokec7 )\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 case\cf4 \strokec4  \cf8 \strokec8 'generate_meta'\cf7 \strokec7 :\cf4 \cb1 \strokec4 \
\cb3             \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 "Based on the following article, write a compelling, SEO-optimized meta description. It must be under 160 characters.\\n\\n\{$article_content\}"\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3             \cf6 \strokec6 break\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 case\cf4 \strokec4  \cf8 \strokec8 'generate_tags'\cf7 \strokec7 :\cf4 \cb1 \strokec4 \
\cb3             \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 "Based on the following article, suggest 5 to 7 relevant SEO tags or keywords, separated by commas.\\n\\n\{$article_content\}"\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3             \cf6 \strokec6 break\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3         \cf6 \strokec6 case\cf4 \strokec4  \cf8 \strokec8 'generate_social'\cf7 \strokec7 :\cf4 \cb1 \strokec4 \
\cb3             \cf10 \strokec10 $prompt\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  \cf8 \strokec8 "Based on the following article, write a punchy and engaging teaser for X (formerly Twitter). Include 2-3 relevant hashtags.\\n\\n\{$article_content\}"\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3             \cf6 \strokec6 break\cf7 \strokec7 ;\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf6 \strokec6 empty\cf7 \strokec7 (\cf10 \strokec10 $prompt\cf7 \strokec7 ))\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 ([\cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'Invalid tune-up action.'\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     \cf10 \strokec10 $ai_response\cf4 \strokec4  \cf7 \strokec7 =\cf4 \strokec4  aisw_call_openai_api\cf7 \strokec7 (\cf10 \strokec10 $prompt\cf7 \strokec7 ,\cf4 \strokec4  \cf10 \strokec10 $max_tokens\cf7 \strokec7 );\cf4 \cb1 \strokec4 \
\cb3     \cf6 \strokec6 if\cf4 \strokec4  \cf7 \strokec7 (\cf4 \strokec4 is_wp_error\cf7 \strokec7 (\cf10 \strokec10 $ai_response\cf7 \strokec7 ))\cf4 \strokec4  \cf7 \strokec7 \{\cf4 \cb1 \strokec4 \
\cb3         wp_send_json_error\cf7 \strokec7 ([\cf8 \strokec8 'message'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf8 \strokec8 'API Error: '\cf4 \strokec4  \cf7 \strokec7 .\cf4 \strokec4  \cf10 \strokec10 $ai_response\cf7 \strokec7 ->\cf4 \strokec4 get_error_message\cf7 \strokec7 ()]);\cf4 \cb1 \strokec4 \
\cb3     \cf7 \strokec7 \}\cf4 \cb1 \strokec4 \
\
\cb3     wp_send_json_success\cf7 \strokec7 ([\cf8 \strokec8 'data'\cf4 \strokec4  \cf7 \strokec7 =>\cf4 \strokec4  \cf10 \strokec10 $ai_response\cf7 \strokec7 ]);\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf7 \cb3 \strokec7 \}\cf4 \cb1 \strokec4 \
\pard\pardeftab720\partightenfactor0
\cf4 \cb3 add_action\cf7 \strokec7 (\cf4 \strokec4  \cf8 \strokec8 'wp_ajax_aisw_tuneup_action'\cf7 \strokec7 ,\cf4 \strokec4  \cf8 \strokec8 'aisw_handle_tuneup_actions'\cf4 \strokec4  \cf7 \strokec7 );\cf4 \cb1 \strokec4 \
}