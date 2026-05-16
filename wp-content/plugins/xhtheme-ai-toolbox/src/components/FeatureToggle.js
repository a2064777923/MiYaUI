import React from 'react';
// 导入WordPress国际化函数
import { __ } from '@wordpress/i18n';

const FeatureToggle = ({ 
  checked, 
  onChange, 
  enabledLabel = __('Enabled', 'xhtheme-ai-toolbox'), 
  disabledLabel = __('Disabled', 'xhtheme-ai-toolbox'),
  disabled = false,
  disabledMessage = ''
}) => {
  return (
    <div className="xht-flex xht-items-center xht-space-x-3">
      <button 
        onClick={disabled ? undefined : onChange}
        className={`xht-relative xht-inline-flex xht-h-6 xht-w-11 xht-items-center xht-rounded-full ${
          checked ? 'xht-bg-blue-500' : 'xht-bg-gray-300'
        } ${disabled ? 'xht-opacity-70 xht-cursor-not-allowed' : 'xht-cursor-pointer'}`}
        disabled={disabled}
      >
        <span 
          className={`${
            checked ? 'xht-translate-x-6' : 'xht-translate-x-1'
          } xht-inline-block xht-h-4 xht-w-4 xht-transform xht-rounded-full xht-bg-white xht-transition`}
        />
      </button>
      <span className={`xht-text-sm ${checked ? 'xht-text-gray-500' : 'xht-text-gray-500'}`}>
        {checked ? enabledLabel : disabledLabel}
      </span>
      {disabled && disabledMessage && (
        <span className="xht-text-xs xht-text-gray-500 xht-italic">
          ({disabledMessage})
        </span>
      )}
    </div>
  );
};

export default FeatureToggle;