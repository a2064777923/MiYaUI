<?php

namespace XHTheme\AIToolbox;

use WP_Post;

/**
 * Intelligent AI Module
 * @author xhtheme
 * @since 2025-03-18
 */
final class XHThemeAi
{

	public $apiUrl = 'https://ai.xhtheme.com/airest/chat';
	public $options = [];
	private static $instance = null;
	private $cached_version = null;
	private $cached_updateTime = null;
	public $userAppiderror = '';
	public $userType = '';
	public $userName = '';
	public $userTypename = '';
	public $quota = null;
	public $userMember = null;

	private static $aiexcerptLoad = false;

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function userTypeName($type)
	{
		switch ($type) {
			case 'member':
				return __('Member users', 'xhtheme-ai-toolbox');
			case 'trial':
				return __('Trial Member', 'xhtheme-ai-toolbox');
			case 'free':
				return __('Free Users', 'xhtheme-ai-toolbox');
		}
		return $type;
	}

	public function userData($types = '')
	{
		if (!$this->userType) {
			$this->getusers();
		}
		switch ($types) {
			case 'type':
				return $this->userType;
			case 'name':
				return $this->userName;
			case 'quota':
				return $this->quota;
			case 'error':
				return $this->userAppiderror;
		}
		return [
			'type' => $this->userType,
			'name' => $this->userName,
			'quota' => $this->quota,
			'error' => $this->userAppiderror
		];
	}

	/**
	 * 判断是否为会员用户
	 * @return bool
	 */
	public function isMember()
	{
		if (is_null($this->userMember)) {
			$this->userMember = in_array($this->userData('type'), ['member', 'trial']);
		}
		return $this->userMember;
	}

	public function getusers($loop = true)
	{
		// 错误处理
		$usererror = get_transient('xhtheme_aitoolbox_users_error');
		if ($usererror) {
			$usererror = json_decode($usererror, true);
			// 获取站点语言
			$current_language = get_locale();
			$language = $current_language ? $current_language : 'default';
			$this->userAppiderror = isset($usererror[$language]) ? $usererror[$language] : $usererror['default'];
			return;
		}

		$userData = [];
		if (get_transient('xhtheme_aitoolbox_users')) {
			$users = get_option('xhtheme_aitoolbox_users', '');
			$users = json_decode($users, true);
			if (!empty($users)) {
				$userData = $users;
			}
		}
		if (isset($userData['usertype'])) {
			$this->userType = sanitize_text_field($userData['usertype']);
			$this->userName = sanitize_text_field($userData['username']);
			$this->quota = isset($userData['quota']) && is_array($userData['quota']) ? (array) $userData['quota'] : false;
			$endtime = isset($userData['endtime']) ? $userData['endtime'] : 0;
			if (!is_numeric($endtime)) {
				$endtime = strtotime($endtime);
			}
			if ($this->userType == 'free' || $endtime > time()) {
				return;
			}
		}
		if ($this->option('appId')) {
			if ($this->fetchRemoteModelList() && $loop) {
				$this->getusers(false);
			}
		}
	}

	public function option($name, $default = '', $cache = true)
	{
		if (empty($this->options) || !$cache) {
			$this->options = get_option('xhtheme_ai_toolbox_settings', []);
		}
		if (isset($this->options[$name])) {
			return $this->options[$name];
		}
		return $default;
	}

	public function setoption($name, $value)
	{
		$this->options = get_option('xhtheme_ai_toolbox_settings', []);
		$this->options = is_array($this->options) ? $this->options : [];
		$this->options[$name] = $value;
		update_option('xhtheme_ai_toolbox_settings', $this->options);
	}


	/**
	 * 模型错误通知
	 * @param string $errorType 错误类型
	 * @param string $errorMsg  错误信息
	 */
	public function modelError($errorType, $errorMsg = '')
	{
		$admin_email = get_option('admin_email');
		switch ($errorType) {
			case 'appId':
				wp_mail(
					$admin_email,
					__('[XHTheme AI Toolbox] APPID information not configured, scheduled task has been suspended', 'xhtheme-ai-toolbox'),
					__('Due to the failure to obtain APPID information, the AI interface is unavailable. The current scheduled task has been temporarily stopped and will be retried after 2 hours!', 'xhtheme-ai-toolbox')
				);
				break;
			default:
				# code...
				break;
		}
	}

	public function updateModeloption($chatLists, $forarr = '')
	{
		if (!$chatLists)
			return false;
		$adminNotice = true;
		$model = $this->option('modelType', 'auto');
		if ($model == 'auto')
			return false;
		foreach ($chatLists as $chat) {
			if ($chat['name'] == $model) {
				$adminNotice = false;
				break;
			}
		}

		if ($adminNotice) {
			// 更新配置
			$this->setoption('modelType', 'auto');
			/**
			 * 发送邮件通知管理员
			 * @param string $admin_email 管理员邮箱
			 */
			$admin_email = get_option('admin_email');
			if ($admin_email) {
				wp_mail(
					$admin_email,
					__('AI model list has been updated, your specified model is invalid and the system has switched to automatic mode', 'xhtheme-ai-toolbox'),
					__('Due to the update of the AI model list, the model you specified for specific functions has become invalid or changed. The system has automatically switched to automatic selection mode for you. If you need to specify a model, please go to the backend AI configuration page to reselect. Thank you for your support!', 'xhtheme-ai-toolbox')
				);
			}
		}
	}

	public function ai_postexcerpt($content)
	{
		if (!is_main_query()) {
			return $content;
		}


		// 防止重复
		if (self::$aiexcerptLoad) {
			return $content;
		}

		self::$aiexcerptLoad = true;

		// 在文章顶部显示摘要
		$aiExcerpt = $this->option('summaryStyle', 'simple');
		$excerptShow = (int) get_post_meta(get_the_ID(), '_excerptai', true);
		if ($aiExcerpt == 'none' || !$excerptShow) {
			return $content;
		}

		global $post;
		if ($excerptShow == 2) {
			$postExcerpt = get_post_meta($post->ID, '_xhai_excerpt', true);
			if (empty($postExcerpt)) {
				delete_post_meta($post->ID, '_excerptai');
				return $content;
			}
		} else {
			$postExcerpt = esc_html($post->post_excerpt);
			if (empty($postExcerpt)) {
				delete_post_meta($post->ID, '_excerptai');
				return $content;
			}
		}


		$newContent = '';
		$aiTitle = $this->option('summaryTitle', '');
		$aiDesc = $this->option('summaryDesc', '');
		$aiDataargs = [
			'title' => $aiTitle ? esc_html($aiTitle) : '',
			'desc' => $aiDesc ? esc_html($aiDesc) : '',
			'style' => $aiExcerpt
		];
		if (apply_filters('xhtheme_ai_toolbox_postexcerpt_show', false, $aiExcerpt) && $aiExcerpt !== 'custom') {
			$newContent .= apply_filters('xhtheme_ai_toolbox_postexcerpt_before', '', $postExcerpt, $aiDataargs);
		} else {
			$newContent .= $this->aiExcerptHtml($postExcerpt, $aiDataargs);
		}
		$newContent .= $content;
		return $newContent;
	}

	public function aiExcerptHtml($postExcerpt, $aiargs)
	{
		/**
		 * 组合模块
		 */
		$aititle = '';
		$aidesc = '';
		if (!empty($aiargs['title'])) {
			$aisvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="xh-svg" viewBox="0 0 16 16"><path d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828l.645-1.937zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.734 1.734 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.734 1.734 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.734 1.734 0 0 0 3.407 2.31l.387-1.162zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L10.863.1z"/></svg>';
			$aititle = sprintf(
				'<div class="eb-titlebox">
					<span class="eb-titicon">%s</span>
					<span class="eb-title">%s</span>
					<span class="eb-icon"><svg t="1742538455943" class="xh-svg" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="71517" width="24" height="24"><path d="M554.144375 443.52552083L655.7121875 53.33333333 200 559.25333333l237.6478125 55.68-101.52 390.24 455.664375-505.96875z" p-id="71518" fill="currentColor"></path></svg></span>
				</div>
				',
				$aisvg,
				sanitize_text_field($aiargs['title'])
			);
		}

		$aiexcerpt = sprintf(
			'<div class="eb-excerpt">%s</div>',
			$postExcerpt
		);

		if (!empty($aiargs['desc'])) {
			$aidesc = sprintf('<div class="eb-desc"><span>— %s</span></div>', $aiargs['desc']);
		}
		return sprintf(
			'<div class="eb-card eb-%s">
				<div class="eb-card-bgbox"></div>
				<div class="eb-card-body">
					%s
				</div>
			</div>',
			esc_attr($aiargs['style']),
			$aititle . $aiexcerpt . $aidesc
		);
	}

	public function modelrestUpdate($request)
	{
		$response = ['success' => true, 'msg' => __('Model list updated!', 'xhtheme-ai-toolbox'), 'data' => []];
		$userArgs = $request->get_param('userArgs');
		if (!empty($userArgs) && is_array($userArgs)) {
			$this->updateModelList($userArgs);
		} else {
			delete_transient('xhtheme_aitoolbox_apicheck');
			$this->fetchRemoteModelList();
		}
		return rest_ensure_response($response);
	}

	/**
	 * 从远程获取模型列表
	 * @return bool 是否成功获取并更新模型列表
	 */
	public function fetchRemoteModelList($cache = true)
	{
		if (get_transient('xhtheme_aitoolbox_apicheck')) {
			return false;
		}
		set_transient('xhtheme_aitoolbox_apicheck', 1, DAY_IN_SECONDS);
		$respapi = wp_remote_post($this->apiUrl, [
			'headers' => [
				'Referer' => home_url()
			],
			'body' => [
				'type' => 'models',
				'pluginsver' => XHTHEME_AI_TOOLBOX_VERSION,
				'version' => $this->getVersion(),
				'apitoken' => $this->option('appId', '', $cache),
				'timestamp' => time()
			],
			'timeout' => 10,
			'sslverify' => false
		]);
		if (!is_wp_error($respapi)) {
			$body = wp_remote_retrieve_body($respapi);
			$response_data = json_decode($body, true);
			if (is_array($response_data) && isset($response_data['code']) && $response_data['code'] == 0) {
				$chatModels = $response_data['data']['modelList'];
				$this->updateModelList($response_data['data']);
				return $chatModels;
			}
			set_transient('xhtheme_aitoolbox_chatmodels_time', 1, 300);
			set_transient('xhtheme_aitoolbox_chatmodels_lasttime', time(), DAY_IN_SECONDS);
		} else {
			set_transient('xhtheme_aitoolbox_chatmodels_time', 1, 60);
		}
		return false;
	}

	public function updateModelList($userArgs)
	{
		update_option('xhtheme_aitoolbox_chatmodels', json_encode($userArgs['modelList'], JSON_UNESCAPED_UNICODE));
		update_option('xhtheme_aitoolbox_chatversion', $userArgs['version']);
		if (isset($userArgs['users']) && !empty($userArgs['users'])) {
			if (isset($userArgs['users']['error'])) {
				set_transient('xhtheme_aitoolbox_users_error', wp_json_encode($userArgs['users']['error']));
			} else {
				delete_transient('xhtheme_aitoolbox_users_error');
				update_option('xhtheme_aitoolbox_users', json_encode($userArgs['users'], JSON_UNESCAPED_UNICODE));
				set_transient('xhtheme_aitoolbox_users', 1, DAY_IN_SECONDS);
			}
		}
		$this->updateModeloption($userArgs['modelList']);
		set_transient('xhtheme_aitoolbox_chatmodels_time', 1, 300);
		set_transient('xhtheme_aitoolbox_chatmodels_lasttime', time(), DAY_IN_SECONDS);
	}

	public function getVersion()
	{
		if ($this->cached_version === null) {
			$this->cached_version = get_option('xhtheme_aitoolbox_chatversion', '1.0');
		}
		return $this->cached_version;
	}

	public function getModelTime()
	{
		if ($this->cached_updateTime === null) {
			$modelTime = get_transient('xhtheme_aitoolbox_chatmodels_lasttime');
			if (!empty($modelTime)) {
				$modelTime = gmdate('Y-m-d', $modelTime);
			} else {
				$modelTime = 'N/A';
			}
			$this->cached_updateTime = $modelTime;
		}
		return $this->cached_updateTime;
	}

	public function postupdate($request)
	{
		$response = ['success' => true, 'msg' => __('Content information retrieved!', 'xhtheme-ai-toolbox'), 'data' => []];
		if (!current_user_can('edit_posts')) {
			$response['success'] = false;
			$response['msg'] = __('You do not have permission to call the current interface!', 'xhtheme-ai-toolbox');
			return rest_ensure_response($response);
		}
		$params = $request->get_params();
		$postId = isset($params['postId']) ? absint($params['postId']) : 0;
		if (isset($params['description']) && !empty($params['description'])) {
			$response['data']['meta']['_xhai_excerpt'] = trim(wp_kses_post($params['description']));
			$response['data']['meta']['_excerptai'] = 2;
		}
		if (isset($params['tags']) && !empty($params['tags'])) {
			$response['data']['tags'] = [];
			foreach ($params['tags'] as $key => $tagdata) {
				if (empty($tagdata) || !is_array($tagdata) || empty($tagdata['name']))
					continue;
				$tagName = sanitize_text_field($tagdata['name']);
				$tagExists = true;
				if ($tagdata['type'] == 'new' && !term_exists($tagName, 'post_tag')) {
					$tagExists = false;
					$newtag = wp_insert_term($tagName, 'post_tag', [
						'description' => $tagdata['description'],
						'slug' => $tagdata['slug']
					]);
					if (!is_wp_error($newtag)) {
						$response['data']['tags'][] = $newtag['term_id'];
						if (isset($tagdata['title']) && !empty($tagdata['title'])) {
							add_term_meta($newtag['term_id'], '_seotitle', sanitize_text_field($tagdata['title']));
						}
					}
				}

				if ($tagExists) {
					$tagexist = term_exists($tagName, 'post_tag');
					if (!empty($tagexist)) {
						$response['data']['tags'][] = $tagexist['term_id'];
					}
				}
			}
		}
		if ($postId && isset($params['comment']) && !empty($params['comment'])) {
			$comment = XHComment::getInstance();
			$comment->pushComment($postId, $params['comment'], (int) $params['maxcomment'], (int) $params['maxdays']);
		}
		if (isset($params['postslug']) && !empty($params['postslug'])) {
			$response['data']['slug'] = sanitize_title($params['postslug']);
		}
		if (isset($params['postcategory']) && !empty($params['postcategory'])) {
			$response['data']['categories'] = [intval($params['postcategory'])];
		}

		if (isset($params['aiimage']) && !empty($params['aiimage'])) {
			$response['data']['meta']['_aiimage_cueword'] = trim(esc_attr($params['aiimage']));
			$response['data']['meta']['_aiimage_status'] = 1;
		}

		/**
		 * 扩展
		 */
		$newresponse = apply_filters('xhtheme_ai_toolbox_postupdate', $response, $params, $postId);
		return rest_ensure_response($newresponse);
	}

	public function checktag($request)
	{
		$response = ['success' => true, 'message' => __('Tag status retrieved!', 'xhtheme-ai-toolbox'), 'data' => []];
		$tagName = $request->get_param('name');

		if (empty($tagName)) {
			$response['success'] = false;
			$response['message'] = __('Please enter a tag name!', 'xhtheme-ai-toolbox');
			return rest_ensure_response($response);
		}

		// 处理单个标签或多个标签
		if (is_array($tagName)) {
			// 多标签处理
			foreach ($tagName as $tag) {
				$tagExists = term_exists($tag, 'post_tag');
				$response['data'][] = [
					'name' => $tag,
					'exists' => !empty($tagExists)
				];
			}
		} else {
			// 单标签处理（保持向后兼容）
			$tagExists = term_exists($tagName, 'post_tag');
			$response['data']['exists'] = !empty($tagExists);
		}

		return rest_ensure_response($response);
	}

	public function add_postmeta()
	{
		$postMeta_args = [
			'_excerptai' => 'integer',
			'_xhaitool_aitasks' => 'boolean',
			'_xhaitool_aitasks_status' => 'boolean',
			'_aiimage_cueword' => 'string',
			'_aiimage_status' => 'integer',
			'_xhai_excerpt' => 'string'  // 添加自定义摘要字段
		];
		foreach ($postMeta_args as $metakey => $typevalue) {
			register_meta(
				'post',
				$metakey,
				array(
					'show_in_rest' => true,
					'type' => $typevalue,
					'single' => true,
					'auth_callback' => '__return_true'
				)
			);
		}
	}

	public function add_restinit()
	{
		$jsonData = [
			'postupdate' => 'postupdate',
			'dataupdate' => 'modelrestUpdate',
			'checktag' => 'checktag',
			'getimagevl' => 'restGetImagevl'
		];
		foreach ($jsonData as $restName => $callback) {
			register_rest_route(XHTHEME_AI_TOOLBOX_RESTNAME, '/' . $restName, [
				'methods' => 'POST',
				'callback' => [$this, $callback],
				'permission_callback' => function () {
					return current_user_can('edit_posts');
				}
			]);
		}
	}

	/**
	 * REST API: 获取图片临时上传信息
	 */
	public function restGetImagevl($request)
	{
		$content = $request->get_param('content');

		if (empty($content)) {
			return rest_ensure_response(['imagevl' => null]);
		}

		$result = xh_get_imagevl(wp_kses_post($content));
		return rest_ensure_response(['imagevl' => $result]);
	}

	public function chatLists($type = 'admin')
	{
		$newChats = [];
		$chatModels = [];

		$chatModeldata = get_option('xhtheme_aitoolbox_chatmodels');
		if ($chatModeldata) {
			$dataModels = json_decode($chatModeldata, true);
			if ($dataModels && is_array($dataModels)) {
				$chatModels = $dataModels;
			}
		}
		if ((!$chatModels && !get_transient('xhtheme_aitoolbox_chatmodels_time')) || ($chatModels && !get_transient('xhtheme_aitoolbox_chatmodels_lasttime'))) {
			// 远程请求模型列表
			$chatModelData = $this->fetchRemoteModelList();
			if ($chatModelData && is_array($chatModelData)) {
				$chatModels = $chatModelData;
			} else {
				$chatModeldata = get_option('xhtheme_aitoolbox_chatmodels');
				if ($chatModeldata) {
					$dataModels = json_decode($chatModeldata, true);
					if ($dataModels && is_array($dataModels)) {
						$chatModels = $dataModels;
					}
				}
			}
		}

		// 当前后台语言
		$currentLang = get_locale();

		if ($type == 'localize') {
			// 脚本调用
			$newChats[] = [
				'value' => 'auto',
				'label' => __('Auto Mode', 'xhtheme-ai-toolbox'),
				'badge' => [
					'type' => 'hot',
					'text' => __('Best', 'xhtheme-ai-toolbox')
				]
			];
			if (!empty($chatModels) && is_array($chatModels)) {
				foreach ($chatModels as $chat) {
					if (isset($chat['badge']['text_' . $currentLang])) {
						$chat['badge']['text'] = $chat['badge']['text_' . $currentLang];
						unset($chat['badge']['text_' . $currentLang]);
					}
					if (isset($chat['member']) && is_array($chat['member'])) {
						if (isset($chat['member']['popdata_' . $currentLang])) {
							$chat['member']['popdata'] = $chat['member']['popdata_' . $currentLang];
							unset($chat['member']['popdata_' . $currentLang]);
						}
					}
					$newChats[] = [
						'value' => $chat['name'],
						'label' => isset($chat['label_' . $currentLang]) && !empty($chat['label_' . $currentLang]) ? $chat['label_' . $currentLang] : $chat['label'],
						'badge' => isset($chat['badge']) && !empty($chat['badge']) ? $chat['badge'] : false,
						'member' => isset($chat['member']) && is_array($chat['member']) ? $chat['member'] : false
					];
				}
			}
		} elseif ($type == 'list') {
			//$newChats = $chatModels;
			$newChats = [];
			if (!empty($chatModels) && is_array($chatModels)) {
				foreach ($chatModels as $chat) {
					if (isset($chat['label_' . $currentLang])) {
						$chat['label'] = $chat['label_' . $currentLang];
					}
					$newChats[] = $chat;
				}
			}
		} else {
			$newChats = [];
			if (!empty($chatModels) && is_array($chatModels)) {
				foreach ($chatModels as $chat) {
					if (isset($chat['label_' . $currentLang])) {
						$chat['label'] = $chat['label_' . $currentLang];
					}
					$newChats[] = $chat;
				}
			}
		}
		return $newChats;
	}

	public function chatModels($type)
	{
		// 检测是否关闭标签摘要的生成
		if ($type == 'post') {
			$tagsOff = $this->option('tagEnabled', false);
			$description = $this->option('summaryEnabled', false);
			$comments = $this->option('commentEnabled', false);
			if (!$tagsOff && !$description && !$comments) {
				return 'none';
			}
		}
		$models = $this->option('modelType', 'auto');
		if ($models == 'auto')
			return $models;
		$chatLists = $this->chatLists('list');
		if (!$chatLists || !is_array($chatLists)) {
			return 'auto';
		}
		$retChat = '';
		foreach ($chatLists as $chat) {
			if ($chat['name'] == $models) {
				$retChat = $models;
				break;
			}
		}

		if (empty($retChat)) {
			$this->updateModeloption($chatLists, [$type]);
			$retChat = 'auto';
		}
		return $retChat;
	}

	public function add_enqueue_assets()
	{
		$screen = get_current_screen();
		if ($screen->id == 'widgets')
			return;
		$apiToken = $this->option('appId', '');
		//if(!$apiToken) return;
		$plugin_dir = plugin_dir_path(dirname(__FILE__));
		$plugin_url = plugin_dir_url(dirname(__FILE__));
		$postai_file = $plugin_dir . 'build/blocks.asset.php';
		$usetShow = $this->isMember();
		if (file_exists($postai_file)) {
			$assets = include $postai_file;
			wp_enqueue_script(
				'xhtheme-ai-toolbox-blocks',
				$plugin_url . 'build/blocks.js',
				$assets['dependencies'],
				$assets['version'],
				true
			);

			wp_set_script_translations('xhtheme-ai-toolbox-blocks', 'xhtheme-ai-toolbox', plugin_dir_path(dirname(__FILE__)) . 'languages');

			$modelsPost = $this->chatModels('post');
			$localize = [
				'chatModels' => $this->chatLists('localize'),
				'userType' => $this->userData('type'),
				'userName' => $this->userData('name'),
				'models' => [
					'paragraph' => $this->chatModels('paragraph'),
					'post' => $modelsPost
				],
				'queue' => $this->option('queueEnabled', false),
				'args' => [],
				'paragraph' => $this->option('paragraphCapacity', ['optimize']),
				'version' => $this->getVersion(),
				'apiurl' => $this->apiUrl,
				'apivar' => XHTHEME_AI_TOOLBOX_APIVERSION,
				'language' => xh_post_language(),
				//'imagerec'  => $this->option('imageRecognition',true),
				'imagerec' => true,
				'apitoken' => $apiToken,
				'aitaskoption' => false,
				'aitaskoptionLink' => admin_url('admin.php?page=xhtheme-ai-automate')
			];

			/**
			 * 判断自动化配置是否完成
			 */
			$localize['aitasks'] = xh_postType_config($screen->post_type, 'queue');
			if (xh_postType_config($screen->post_type, 'tasks')) {
				$localize['aitaskoption'] = true;
			}


			if ($modelsPost !== 'none') {
				$localize['args']['post'] = [];

				// 元数据补全
				$primaryMeta = [];
				$postTypedata = xh_postTypedata($screen->post_type);
				$postTypeTasks = $postTypedata && isset($postTypedata['tasks']) && is_array($postTypedata['tasks']) ? $postTypedata['tasks'] : [];
				if ($this->option('primarySlug', false) && in_array('slug', $postTypeTasks)) {
					$primaryMeta['postslug'] = true;
				}
				if ($this->option('primaryCategory', false) && in_array('category', $postTypeTasks)) {
					// 获取所有分类
					$categories = xh_postType_config($screen->post_type, 'categories');
					if ($categories) {
						$taxonomy = $categories;
						$categories = get_categories([
							'taxonomy' => $taxonomy,
							'hide_empty' => false,
							'orderby' => 'name',
							'order' => 'ASC'
						]);
						if (!empty($categories) && is_array($categories)) {
							$terms = [];
							$default_category = get_option('default_' . $taxonomy);
							if ($default_category) {
								$default_term = get_term($default_category, $taxonomy);
								$default_termid = $default_term ? $default_term->term_id : 0;
							} else {
								$default_termid = 0;
							}
							// 构建分类数组
							foreach ($categories as $category) {
								$terms[] = [
									'id' => $category->term_id,
									'name' => $category->name
								];
							}

							$primaryMeta['postterms'] = [
								'terms' => $terms,
								'default' => $default_termid
							];
						}
					}
				}

				// 话题
				if ($usetShow && $this->option('primaryThread', false) && in_array('thread', $postTypeTasks)) {
					$threadNumber = $this->option('primaryThreadNumber', 3);
					$threadTypes = $this->option('primaryThreadTypes', ['question', 'definition']);
					$threadPerspectives = $this->option('primaryThreadPerspectives', ['blogger', 'expert']);
					if ($threadNumber) {
						$primaryMeta['postthread'] = [
							'number' => $threadNumber,
							'types' => $threadTypes,
							'perspectives' => $threadPerspectives
						];
					}
				}
				if (!empty($primaryMeta) && is_array($primaryMeta)) {
					$localize['args']['post']['primary'] = $primaryMeta;
				}

				// 摘要			
				if ($this->option('summaryEnabled', false) && in_array('summary', $postTypeTasks)) {
					$localize['args']['post']['excerpt'] = [
						'length' => (int) $this->option('summaryMaxLength', 150),
						'model' => $this->option('summaryModel', 'summary')
					];
				}

				// 标签
				if ($this->option('tagEnabled', false) && in_array('tags', $postTypeTasks)) {
					$localize['args']['post']['tags'] = [
						'number' => [
							'min' => max(1, $this->option('tagMinCount', 0)),
							'max' => max(1, $this->option('tagMaxCount', 6))
						],
						'seotitle' => (int) xh_option('tagSeoTitle', false)
					];
					$expand = (bool) $this->option('tagExpandDefault', false);
					$localize['tagExpand'] = $expand;
				}

				// 评论	
				if ($usetShow && $this->option('commentEnabled', false) && in_array('comments', $postTypeTasks)) {
					$commentArgs = [
						'number' => 0,
						'loadnum' => 0,
						'day' => 0
					];
					$commentNumber = 1;
					$commentMinCount = max(1, $this->option('commentMinCount', 0));
					$commentMaxCount = max(1, $this->option('commentMaxCount', 0));
					if ($commentMaxCount > $commentMinCount) {
						$commentNumber = wp_rand($commentMinCount, $commentMaxCount);
					}
					$groupNumber = $this->option('commentGroup', 3);
					$commentArgs['allnumber'] = $commentNumber;
					if ($commentNumber > $groupNumber) {
						$commentArgs['number'] = $groupNumber;
						$commentArgs['loadnum'] = $commentNumber - $groupNumber;
					} else {
						$commentArgs['number'] = $commentNumber;
						$commentArgs['loadnum'] = 0;
					}
					$commentArgs['day'] = $this->option('commentMaxday', 1) ?: 1;

					$localize['args']['post']['comment'] = $commentArgs;
				}

				// 文生图
				if ($usetShow && $this->option('imageThumb', false) && in_array('aiimage', $postTypeTasks)) {
					$localize['args']['post']['aiimage'] = [
						'style' => $this->option('imageStyle', 'auto'),
						'size' => $this->option('imageSize', '1280x768'),
					];
				}
			}
			wp_localize_script('xhtheme-ai-toolbox-blocks', 'xhthemeAiToolboxBlock', $localize);
		}
	}

	function rest_aitasks_field_saved($post, $request)
	{
		// 如果 REST 请求中包含该字段，就按正常流程保存
		if (isset($request['meta']['_xhaitool_aitasks'])) {
			return;
		}
		add_post_meta($post->ID, '_xhaitool_aitasks', 0, true);
	}

	function filter_insert_excerpt($text, $post)
	{
		if (!empty($text))
			return $text;
		$excerptShow = (int) get_post_meta($post->ID, '_excerptai', true);
		$ExcerptOption = $this->option('summaryExportFields', []);
		if ($excerptShow == 2 && in_array('postExcerpt', $ExcerptOption)) {
			$postExcerpt = get_post_meta($post->ID, '_xhai_excerpt', true);
			if (!empty($postExcerpt)) {
				return $postExcerpt;
			}
		}
		remove_filter('the_content', [$this, 'ai_postexcerpt'], 99);
		return $text;
	}

	function filter_display_excerpt($value, $post_id, $context)
	{
		if (!empty($value))
			return $value;
		$excerptShow = (int) get_post_meta($post_id, '_excerptai', true);
		$ExcerptOption = $this->option('summaryExportFields', []);
		if ($excerptShow == 2 && in_array('postExcerpt', $ExcerptOption)) {
			$postExcerpt = get_post_meta($post_id, '_xhai_excerpt', true);
			if (!empty($postExcerpt)) {
				return $postExcerpt;
			}
		}
		return $value;
	}

	function filter_excerpt($text, $post)
	{
		add_filter('the_content', [$this, 'ai_postexcerpt'], 99, 1);
		if (is_single() && is_main_query()) {
			$aiExcerpt = $this->option('summaryStyle', 'simple');
			$excerptShow = (int) get_post_meta($post->ID, '_excerptai', true);
			if ($aiExcerpt !== 'none' && $excerptShow == 1) {
				$text = '';
			}
		}
		return $text;
	}

	// 在标签编辑页面添加字段 + 标题
	public function edit_tag_form_fields($term, $taxonomy)
	{
		if (!xh_option('tagSeoTitle', false)) {
			return;
		}
		if ($taxonomy != 'post_tag') {
			$taxonomy_obj = get_taxonomy($taxonomy);
			if ($taxonomy_obj && $taxonomy_obj->hierarchical) {
				return;
			}
		}
		if (apply_filters('xhtheme_ai_toolbox_tag_title_field', false, $taxonomy))
			return;
		$tagseotitle = get_term_meta($term->term_id, '_seotitle', true);
?>
		<tr class="form-field">
			<th colspan="2">
				<h2 style="margin: 1em 0 0.5em; font-size: 16px; color: #0073aa;">
					<?php esc_html_e('XHTheme AI Toolbox', 'xhtheme-ai-toolbox'); ?>
				</h2>
			</th>
		</tr>
		<tr class="form-field">
			<th scope="row"><label
					for="xhtheme_ai_toolbox_tag_title"><?php esc_html_e('Custom Title', 'xhtheme-ai-toolbox'); ?></label>
			</th>
			<td>
				<input name="xhtheme_ai_toolbox_tag_title" id="xhtheme_ai_toolbox_tag_title" type="text"
					value="<?php echo esc_attr($tagseotitle); ?>" class="regular-text" />
				<p class="description">
					<?php esc_html_e('Used to replace the HTML title ( &lt;title&gt; ) content of the tag page.', 'xhtheme-ai-toolbox'); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field">
			<th colspan="2">
				<h2 style="margin: 1em 0 0.5em; font-size: 16px; color: #0073aa;">—— END ——</h2>
			</th>
		</tr>
	<?php
	}

	public function edit_term_save($term_id)
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core in term edit
		if (isset($_POST['xhtheme_ai_toolbox_tag_title'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WordPress core in term edit
			update_term_meta($term_id, '_seotitle', sanitize_text_field(wp_unslash($_POST['xhtheme_ai_toolbox_tag_title'])));
		}
	}

	public function document_title_parts($title)
	{
		if (is_tax() || is_tag()) {
			$term = get_queried_object();
			// 改成 in_array
			if (in_array($term->taxonomy, ['post_tag', 'product_tag', 'sitetag'])) {
				$custom_title = get_term_meta($term->term_id, '_seotitle', true);
				if ($custom_title) {
					if ($title['title'] == $term->name) {
						$title['title'] = $custom_title;
					}
				}
			}
		}
		return $title;
	}


	/**
	 * 添加AI摘要编辑元框
	 */
	public function add_ai_excerpt_metabox()
	{
		// 获取支持摘要的所有文章类型        
		add_meta_box(
			'xhtheme_ai_excerpt_metabox',
			__('AI Excerpt', 'xhtheme-ai-toolbox'),
			[$this, 'render_ai_excerpt_metabox'],
			xh_postTypes(),
			'side',
			'default',
			[
				'__back_compat_meta_box' => true
			]
		);
	}

	/**
	 * 渲染AI摘要编辑元框
	 */
	public function render_ai_excerpt_metabox($post)
	{
		// 获取AI自定义摘要
		$ai_excerpt = get_post_meta($post->ID, '_xhai_excerpt', true);

		// 如果没有生成AI摘要，显示提示信息
		if (empty($ai_excerpt)) {
			echo '<p class="description">';
			esc_html_e('No AI excerpt has been generated yet.', 'xhtheme-ai-toolbox');
			echo '</p>';
			return;
		}

		// 添加nonce进行安全验证
		wp_nonce_field('xhtheme_ai_excerpt_metabox', 'xhtheme_ai_excerpt_nonce');

		// 显示编辑区域
	?>
		<div class="xhtheme-ai-excerpt-wrapper">
			<textarea name="xhtheme_xhai_excerpt" id="xhtheme_xhai_excerpt" class="large-text" rows="5"
				style="width: 100%;"><?php echo esc_textarea($ai_excerpt); ?></textarea>
			<p class="description">
				<?php esc_html_e('This AI-generated excerpt will be displayed at the beginning of your post if enabled.', 'xhtheme-ai-toolbox'); ?>
			</p>
		</div>
		<style>
			.xhtheme-ai-excerpt-wrapper {
				margin-top: 10px;
			}

			.xhtheme-ai-excerpt-controls {
				margin-bottom: 10px;
			}
		</style>
<?php
	}

	/**
	 * 保存AI摘要元框数据
	 */
	public function save_ai_excerpt_metabox($post_id, $post)
	{
		// 安全检查
		if (!isset($_POST['xhtheme_ai_excerpt_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xhtheme_ai_excerpt_nonce'])), 'xhtheme_ai_excerpt_metabox')) {
			return;
		}

		// 如果是自动保存，不处理
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// 检查权限
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// 保存AI自定义摘要内容
		if (isset($_POST['xhtheme_xhai_excerpt'])) {
			$ai_excerpt = wp_kses_post(wp_unslash($_POST['xhtheme_xhai_excerpt']));
			if (empty($ai_excerpt)) {
				delete_post_meta($post_id, '_xhai_excerpt');
				delete_post_meta($post_id, '_excerptai');
			} else {
				update_post_meta($post_id, '_xhai_excerpt', trim($ai_excerpt));
			}
		}
	}

	public function cronFetchRemoteModelList()
	{
		if (!wp_cache_add('modelist', 1, 'xhaitools', HOUR_IN_SECONDS)) return;
		delete_transient('xhtheme_aitoolbox_apicheck');
		$this->fetchRemoteModelList(false);
	}

	public function init()
	{
		// AI功能
		add_action('init', [$this, 'add_postmeta']);
		add_action('enqueue_block_editor_assets', [$this, 'add_enqueue_assets']);
		add_action('rest_api_init', [$this, 'add_restinit']);
		add_action('rest_after_insert_post', [$this, 'rest_aitasks_field_saved'], 10, 2);
		add_filter('the_content', [$this, 'ai_postexcerpt'], 99, 1);
		add_filter('get_the_excerpt', [$this, 'filter_insert_excerpt'], 5, 2);
		add_filter('get_the_excerpt', [$this, 'filter_excerpt'], 99, 2);
		add_filter('post_excerpt', [$this, 'filter_display_excerpt'], 10, 3);

		// AI标签
		add_action('post_tag_edit_form_fields', [$this, 'edit_tag_form_fields'], 99, 2);
		add_action('product_tag_edit_form_fields', [$this, 'edit_tag_form_fields'], 99, 2);
		add_action('sitetag_edit_form_fields', [$this, 'edit_tag_form_fields'], 99, 2);
		add_action('edit_term', [$this, 'edit_term_save']);
		add_filter('document_title_parts', [$this, 'document_title_parts'], 50, 1);

		// 添加AI摘要编辑框
		add_action('add_meta_boxes', [$this, 'add_ai_excerpt_metabox']);
		add_action('save_post', [$this, 'save_ai_excerpt_metabox'], 10, 2);

		// 添加定时任务
		add_action('xhaitoolbox_twicedaily_cron', [$this, 'cronFetchRemoteModelList']);

		/**
		 * 摘要适配
		 */
		add_filter('zib_get_excerpt', [$this, 'zib_excerpt'], 10, 2);
	}


	/**
	 * 适配类代码
	 */
	public function zib_excerpt($excerpt, $post)
	{
		if (function_exists('zib_get_post_meta')) {
			$newexcerpt = trim(zib_get_post_meta($post->ID, 'description', true));
			if (!empty($newexcerpt)) {
				return $newexcerpt;
			}
		}
		$post_id     = $post->ID;
		$excerptShow = (int)get_post_meta($post_id, '_excerptai', true);
		$ExcerptOption = $this->option('summaryExportFields', []);
		if ($excerptShow == 2 && in_array('postExcerpt', $ExcerptOption)) {
			$postExcerpt = get_post_meta($post_id, '_xhai_excerpt', true);
			if (!empty($postExcerpt)) {
				return $postExcerpt;
			}
		}
		return $excerpt;
	}
}
