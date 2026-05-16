import { createRoot } from 'react-dom/client';
import { useState, useEffect, useRef, createContext, useContext } from 'react';
import { RadioControl } from '@wordpress/components';
import { settingsI18n } from './components/i18n';
import './css/tailwind.css'; 
import RangeSlider from './components/RangeSlider';
import FeatureToggle from './components/FeatureToggle';

const SettingsContext = createContext();

const SettingItem = ({ title, description, children, tips = '' }) => (
  <div className="xht-mb-8 xht-px-3 xht-pt-6 xht-flex xht-flex-col md:xht-flex-row">
    <div className="xht-w-full md:xht-w-1/4 xht-pr-4">
      <h3 className="xht-text-base xht-font-medium xht-mb-2">{title}</h3>      
    </div>
    <div className="xht-w-full md:xht-w-3/4">
      {children}
      { tips && (
        <div 
          className="xht-inline-block xht-p-2 xht-rounded xht-text-xs xht-bg-gray-200 xht-mt-3 xht-text-emerald-600"
          dangerouslySetInnerHTML={{ __html: tips }}
        />
      )}
      <div 
        className="xht-text-xs xht-mt-3 xht-text-gray-500 xht-mb-3 md:xht-mb-0"
        dangerouslySetInnerHTML={{ __html: description }}
      />
    </div>
  </div>
);

const TabButton = ({ active, onClick, children, id }) => {
	return (
	  <a
		href={`#${id}`}
		onClick={(e) => {
		  e.preventDefault();
		  onClick();
		  const element = document.getElementById(id);
		  if (element) {
		    const offset = window.innerWidth > 768 ? 100 : 60;
		    const elementPosition = element.getBoundingClientRect().top + window.scrollY;
		    window.scrollTo({
		      top: elementPosition - offset,
		      behavior: 'smooth'
		    });
		  }
		}}
		className={`xht-px-4 xht-py-2 xht-font-medium xht-rounded-t-lg xht-transition-colors ${
		  active 
			? 'xht-bg-gray-100 xht-text-blue-600 xht-border-b-2 xht-border-blue-600 focus:xht-text-blue-600' 
			: 'xht-text-gray-600 hover:xht-text-blue-600'
		}`}
	  >
		{children}
	  </a>
	);
};

const BasicConfig = () => {
  const { settings, updateSettings } = useContext(SettingsContext);
  
  return (
    <div className="xht-bg-white xht-rounded-lg xht-pr-4 xht-pb-4 xht-mb-5 xht-overflow-hidden" id="basic">
      <div className="xht-text-white xht-mb-3 xht-relative">
        <span className="xht-inline-block xht-relative xht-outside-circle">					
          <span className="xht-inline-block xht-px-3 xht-py-2 xht-bg-blue-400 xht-rounded-br-xl">
            {settingsI18n.basicConfig}
          </span>
        </span>
      </div>
      <div className="xht-pl-4">
        <SettingItem 
          title="APPID" 
          description={settingsI18n.appIdDesc(settingsI18n.getAppId)}
        >
          <input 
            type="text" 
            className="xht-w-full xht-max-w-md !xht-px-3 !xht-py-1 xht-border xht-border-gray-300 xht-rounded-md focus:xht-outline-none focus:xht-ring-2 focus:xht-ring-blue-500" 
            value={settings.appId}
            onChange={(e) => updateSettings('appId', e.target.value)}
            placeholder={settingsI18n.appId}
          />
        </SettingItem>

        <SettingItem 
          title={settingsI18n.defaultModel}
          description={settingsI18n.modelDesc}
        >
          <RadioControl
            selected={settings.modelType}
            options={window.xhthemeAiToolbox?.modelList || [
              { label: settingsI18n.autoSelect, value: 'auto' }
            ]}
            onChange={(value) => updateSettings('modelType', value)}
          />
        </SettingItem>

        <div className="xht-text-xs xht-text-gray-500 xht-bg-yellow-50 xht-p-3 xht-rounded xht-border xht-border-yellow-100 xht-mt-6">
          {settingsI18n.apiNote}
        </div>
      </div>
    </div>
  );
};

const AIParagraph = () => {
  const { settings, updateSettings } = useContext(SettingsContext);
    
  return (
    <div className="xht-bg-white xht-rounded-lg xht-pr-4 xht-pb-4 xht-mb-5 xht-overflow-hidden" id="paragraph">
      <div className="xht-text-white xht-mb-3 xht-relative">
        <span className="xht-inline-block xht-relative xht-outside-circle">					
          <span className="xht-inline-block xht-px-3 xht-py-2 xht-bg-blue-400 xht-rounded-br-xl">
            {settingsI18n.paragraphTitle}
          </span>
        </span>
      </div>
      <div className="xht-pl-4">
        <SettingItem 
          title={settingsI18n.paragraphFeature}
          description={settingsI18n.paragraphDesc}
        >
          <FeatureToggle
            checked={true}
            onChange={() => {}}
            disabled={true}
            disabledMessage={settingsI18n.coreFeature}
          />
        </SettingItem>
      </div>
    </div>
  );
};

const AISummary = () => {
  const { settings, updateSettings } = useContext(SettingsContext);
  
  return (
    <div className="xht-bg-white xht-rounded-lg xht-pr-4 xht-pb-4 xht-mb-5 xht-overflow-hidden" id="summary">
      <div className="xht-text-white xht-mb-3 xht-relative">
        <span className="xht-inline-block xht-relative xht-outside-circle">					
          <span className="xht-inline-block xht-px-3 xht-py-2 xht-bg-blue-400 xht-rounded-br-xl">
            {settingsI18n.summaryTitle}
          </span>
        </span>
      </div>  
      <div className="xht-pl-4">
        <SettingItem 
          title={settingsI18n.summaryFeature}
          description={settingsI18n.summaryDesc}
        >
          <FeatureToggle
            checked={settings.summaryEnabled}
            onChange={() => updateSettings('summaryEnabled', !settings.summaryEnabled)}
          />
        </SettingItem>

        {settings.summaryEnabled && (
          <>
            <SettingItem 
              title={settingsI18n.maxSummaryLength}
              description={settingsI18n.summaryLengthDesc}
            >
              <RangeSlider 
                singleMode={true}
                min={50}
                max={300}
                step={10}
                value={settings.summaryMaxLength || 150}
                onChange={(value) => updateSettings('summaryMaxLength', value)}
                label={settingsI18n.wordCount}
              />
            </SettingItem>
          </>
        )}
      </div>
    </div>
  );
};

const AITags = () => {
  const { settings, updateSettings } = useContext(SettingsContext);
  
  return (
    <div className="xht-bg-white xht-rounded-lg xht-pr-4 xht-pb-4 xht-mb-5 xht-overflow-hidden" id="tags">
      <div className="xht-text-white xht-mb-3 xht-relative">
        <span className="xht-inline-block xht-relative xht-outside-circle">					
          <span className="xht-inline-block xht-px-3 xht-py-2 xht-bg-blue-400 xht-rounded-br-xl">
            {settingsI18n.tagsTitle}
          </span>
        </span>
      </div> 
      <div className="xht-pl-4">
        <SettingItem 
          title={settingsI18n.tagsFeature}
          description={settingsI18n.tagsDesc}
        >
          <FeatureToggle
            checked={settings.tagEnabled}
            onChange={() => updateSettings('tagEnabled', !settings.tagEnabled)}
          />
        </SettingItem>

        {settings.tagEnabled && (
          <>
            <SettingItem 
              title={settingsI18n.tagsRange}
              description={settingsI18n.tagsRangeDesc}
            >
              <RangeSlider 
                min={1}
                max={6}
                step={1}
                minDistance={1}
                minValue={settings.tagMinCount || 2}
                maxValue={settings.tagMaxCount || 4}
                onMinChange={(value) => updateSettings('tagMinCount', value)}
                onMaxChange={(value) => updateSettings('tagMaxCount', value)}
                minLabel={settingsI18n.minimum}
                maxLabel={settingsI18n.maximum}
              />
            </SettingItem>

            <SettingItem 
              title={settingsI18n.newTagStatus}
              description={settingsI18n.newTagDesc}
            >
              <FeatureToggle
                checked={settings.tagExpandDefault}
                onChange={() => updateSettings('tagExpandDefault', !settings.tagExpandDefault)}
                enabledLabel={settingsI18n.defaultExpand}
                disabledLabel={settingsI18n.defaultCollapse}
              />
            </SettingItem>
          </>
        )}
      </div>
    </div>
  );
};

const SaveButton = ({ isSaving, onClick, saveStatus }) => {
  const getStatusMessage = () => {
    if (saveStatus === 'saved') {
      return settingsI18n.saveSuccess;
    } else if (saveStatus === 'saving') {
      return settingsI18n.pluginDev('XHTheme');
    } else {
      return settingsI18n.settingsChanged;
    }
  };

  return (
    <div className="xht-sticky xht-bottom-0 xht-z-50 xht-bg-white xht-py-4 xht-rounded-lg xht-shadow-[0_-4px_8px_-1px_rgba(0,0,0,0.05)]">
      <div className="xht-container xht-mx-auto xht-max-w-6xl xht-px-4 xht-flex xht-items-center xht-justify-between">
        <div className="xht-flex xht-items-center xht-space-x-2">        
          {isSaving ? (
            <>
              <span className={`xht-text-sm`}>
                {settingsI18n.saving}
              </span>
              <svg className="xht-animate-spin xht-h-4 xht-w-4 xht-text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="xht-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="xht-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>            
            </>
          ) : (
            <span 
              className={`xht-text-sm ${
                saveStatus === 'saved' ? 'xht-text-blue-600' : saveStatus === 'saving' ? '' : 'xht-text-green-600'
              }`}
              dangerouslySetInnerHTML={{ __html: getStatusMessage() }}
            />
          )}
        </div>
        <button 
          onClick={onClick}
          disabled={isSaving}
          className={`xht-bg-blue-600 xht-text-white xht-px-6 xht-py-2 xht-rounded-md xht-hover:xht-bg-blue-700 xht-transition-all xht-font-medium ${isSaving ? 'xht-opacity-80' : ''}`}
        >
          {isSaving ? 'Loading...' : settingsI18n.saveAll }
        </button>
      </div>
    </div>
  );
};

const AIToolboxApp = () => {
  const [activeTab, setActiveTab] = useState('basic');
  const [isSticky, setIsSticky] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const tabsRef = useRef(null);

  const [saveStatus, setSaveStatus] = useState('saving');
  const [isInitialized, setIsInitialized] = useState(false);
  const initialSettings = window.xhthemeAiToolbox?.settings || {};
  
  const [settings, setSettings] = useState({
    appId: '',
    modelType: 'auto',
    tagEnabled: true,
    tagMinCount: 2,
    tagMaxCount: 4,
    summaryEnabled: true,
    summaryMaxLength: 150,
    ...initialSettings
  });

  const updateSettings = (key, value) => {
    setSettings(prevSettings => ({
      ...prevSettings,
      [key]: value
    }));
  };

  const saveSettings = () => {
    setIsSaving(true);    
    fetch( window.xhthemeAiToolbox.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'save_xhtheme_ai_settings',
        nonce: window.xhthemeAiToolbox?.nonce || '',
        settings: JSON.stringify(settings)
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        if (window.xhthemeAiToolbox) {
          window.xhthemeAiToolbox.settings = settings;
        }
        setSaveStatus('saved');
        setTimeout(() => {
          setSaveStatus('saving');
        }, 3000);
      }
    })
    .catch(error => {
      console.error(settingsI18n.saveError(error.message));
      alert(settingsI18n.saveFailed);
      setSaveStatus('unsaved');
    })
    .finally(() => {
      setTimeout(() => {
        setIsSaving(false);
      }, 1000);
    });
  };

  useEffect(() => {
    if (!isInitialized) {
      setIsInitialized(true);
    } else {
      setSaveStatus('unsaved');
    }
  }, [settings]);

  useEffect(() => {
    const handleScroll = () => {
      if (tabsRef.current) {
        const tabsPosition = tabsRef.current.getBoundingClientRect().top;
        const threshold = window.innerWidth > 768 ? 30 : 10;
        setIsSticky(tabsPosition <= threshold);
      }
      
      const sections = ['basic', 'paragraph', 'summary', 'tags', 'comments'];
      let currentSection = activeTab;
      
      for (const section of sections) {
        const element = document.getElementById(section);
        if (element) {
          const rect = element.getBoundingClientRect();
          const offset = window.innerWidth > 768 ? 120 : 80;
          if (rect.top <= offset && rect.bottom > 0) {
            currentSection = section;
            break;
          }
        }
      }
      
      if (currentSection !== activeTab) {
        setActiveTab(currentSection);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, [activeTab]);

  return (
        <SettingsContext.Provider value={{ settings, updateSettings }}>
      <div className="xht-container xht-mx-auto xht-p-4 xht-max-w-6xl xht-mt-4">
        <h1 className="xht-text-2xl xht-font-bold xht-text-gray-800 xht-pb-6 xht-flex xht-items-center">
          <span className="xht-bg-blue-6 xht-text-white xht-py-0 xht-px-1 xht-rounded xht-mr-2">AI</span>
          {__('Toolbox', 'xhtheme-ai-toolbox')}
          <span className="xht-text-sm xht-font-normal xht-text-gray-5 xht-ml-auto">
            {sprintf(__('Plugin version: %s', 'xhtheme-ai-toolbox'), window.xhthemeAiToolbox.pluginVersion)}
          </span>
        </h1>
        
        <div 
          ref={tabsRef}
          className={`xht-sticky xht-top-0 md:xht-top-[30px] xht-z-50 xht-bg-white xht-rounded-lg ${
            isSticky ? 'xht-shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)]' : 'xht-shadow'
          } xht-mb-6 xht-overflow-hidden`}
        >
          <div className="xht-container xht-mx-auto xht-max-w-6xl xht-overflow-x-auto xht-scrollbar-hide">
            <div className="xht-flex xht-space-x-1 xht-p-2 xht-min-w-max">
              <TabButton 
                active={activeTab === 'basic'} 
                onClick={() => setActiveTab('basic')}
                id="basic"
              >
                {__('Basic Configuration', 'xhtheme-ai-toolbox')}
              </TabButton>
              <TabButton 
                active={activeTab === 'paragraph'} 
                onClick={() => setActiveTab('paragraph')}
                id="paragraph"
              >
                {__('AI Paragraph', 'xhtheme-ai-toolbox')}
              </TabButton>
              <TabButton 
                active={activeTab === 'summary'} 
                onClick={() => setActiveTab('summary')}
                id="summary"
              >
                {__('AI Summary', 'xhtheme-ai-toolbox')}
              </TabButton>
              <TabButton 
                active={activeTab === 'tags'} 
                onClick={() => setActiveTab('tags')}
                id="tags"
              >
                {__('AI Tags', 'xhtheme-ai-toolbox')}
              </TabButton>
            </div>
          </div>
        </div>
        
        {isSticky && <div className="xht-h-[80px]"></div>}
        
        <div className="xht-flex xht-flex-col md:xht-flex-row xht-gap-5">
          <div className="xht-w-full md:xht-w-3/4">
            {(!window.xhthemeAiToolbox.gutenbergActive || window.xhthemeAiToolbox.classicEditorActive) && (
              <div className="xht-text-xs xht-text-white xht-bg-rose-5 xht-p-3 xht-mb-5 xht-rounded xht-flex xht-items-center">
                <svg t="1742364234602" className="xht-icon xht-mr-2 xht-flex-shrink-0" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6194" width="16" height="16">
                  <path d="M170.666667 512a341.333333 341.333333 0 1 1 153.6 285.141333 42.666667 42.666667 0 1 0-46.933334 71.253334A424.874667 424.874667 0 0 0 512 938.666667c235.648 0 426.666667-191.018667 426.666667-426.666667S747.648 85.333333 512 85.333333 85.333333 276.352 85.333333 512c0 82.474667 23.466667 159.573333 64 224.896a42.666667 42.666667 0 0 0 72.533334-45.013333A339.541333 339.541333 0 0 1 170.666667 512z m298.666666-42.666667a42.666667 42.666667 0 1 1 85.333334 0v256a42.666667 42.666667 0 1 1-85.333334 0v-256z m42.666667-213.333333a64 64 0 1 0 0 128 64 64 0 0 0 0-128z" fill="currentColor" p-id="6195"></path>
                </svg>
                <span>{__('The plugin only supports use in the block editor (Gutenberg), not the classic editor', 'xhtheme-ai-toolbox')}</span>
              </div>
            )}
            <BasicConfig />
            <AIParagraph />
            <AISummary />
            <AITags />
            <SaveButton isSaving={isSaving} onClick={saveSettings} saveStatus={saveStatus} />
          </div>
          
          <div className="xht-w-full md:xht-w-1/4">
            <div className="xht-bg-white xht-rounded-lg xht-p-4 xht-mb-5 xht-sticky xht-top-[100px]">
              <div className="xht-mt-6">
                <h4 className="xht-text-sm xht-font-medium xht-mb-4 xht-pb-3 xht-mt-[-1.2rem] xht-text-gray-7 xht-border-b xht-border-gray-2">
                  {__('Help Resources', 'xhtheme-ai-toolbox')}
                </h4>
                <ul className="xht-space-y-1">
                  <li className="xht-mb-3">
                    <a href="#" target="_blank" className="xht-text-sm xht-text-blue-6 xht-hover:xht-text-blue-8 xht-flex xht-items-center">
                      <svg className="xht-w-4 xht-h-4 xht-mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                      </svg>
                      {__('To be supplemented', 'xhtheme-ai-toolbox')}
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </SettingsContext.Provider>
  );
};

const wrapper = document.getElementById('xhtheme-ai-toolbox-wrapper');
if (wrapper) {
  const root = createRoot(wrapper);
  root.render(<AIToolboxApp />);
} else {
  console.error(__('Cannot find DOM element with ID xhtheme-ai-toolbox-wrapper', 'xhtheme-ai-toolbox'));
}