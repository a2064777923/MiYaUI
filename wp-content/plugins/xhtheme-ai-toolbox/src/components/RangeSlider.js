import { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';

const RangeSlider = ({ 
  min = 1, 
  max = 10, 
  step = 1, 
  minDistance = 1, 
  minValue, 
  maxValue, 
  onMinChange, 
  onMaxChange,
  onChange,
  value,
  minLabel = __("Minimum", "xhtheme-ai-toolbox"),
  maxLabel = __("Maximum", "xhtheme-ai-toolbox"),
  label = __("Value", "xhtheme-ai-toolbox"),
  summaryText = "",
  singleMode = false // 是否为单滑块模式
}) => {
  const [dragging, setDragging] = useState(null);
  const trackRef = useRef(null);

  // 计算位置百分比
  const minPos = singleMode 
    ? 0 
    : ((minValue - min) / (max - min)) * 100;
  const maxPos = singleMode 
    ? ((value - min) / (max - min)) * 100 
    : ((maxValue - min) / (max - min)) * 100;

  const handleMouseDown = (handle) => (e) => {
    e.preventDefault();
    setDragging(handle);
  };

  const handleTouchStart = (handle) => (e) => {
    setDragging(handle);
  };

  const updateValue = (clientX) => {
    if (!trackRef.current || !dragging) return;

    const { left, width } = trackRef.current.getBoundingClientRect();
    const percent = Math.min(Math.max((clientX - left) / width, 0), 1);
    const rawValue = min + percent * (max - min);
    const steppedValue = Math.round(rawValue / step) * step;

    if (singleMode) {
      onChange(steppedValue);
    } else {
      if (dragging === "min") {
        const newMin = Math.min(steppedValue, maxValue - minDistance);
        onMinChange(newMin);
      } else {
        const newMax = Math.max(steppedValue, minValue + minDistance);
        onMaxChange(newMax);
      }
    }
  };

  const handleMouseMove = (e) => {
    updateValue(e.clientX);
  };

  const handleTouchMove = (e) => {
    if (e.touches.length > 0) {
      updateValue(e.touches[0].clientX);
    }
  };

  const handleMouseUp = () => {
    setDragging(null);
  };

  useEffect(() => {
    if (dragging) {
      window.addEventListener("mousemove", handleMouseMove);
      window.addEventListener("mouseup", handleMouseUp);
      window.addEventListener("touchmove", handleTouchMove);
      window.addEventListener("touchend", handleMouseUp);
    }

    return () => {
      window.removeEventListener("mousemove", handleMouseMove);
      window.removeEventListener("mouseup", handleMouseUp);
      window.removeEventListener("touchmove", handleTouchMove);
      window.removeEventListener("touchend", handleMouseUp);
    };
  }, [dragging, minValue, maxValue, value]);

  return (
    <div className="xht-w-full xht-px-2 xht-mt-[-5px] xht-flex xht-flex-col">
      <div className="xht-relative xht-mb-10 xht-mt-5 xht-w-full" style={{ maxWidth: '400px' }}>
        {/* 滑块轨道 */}
        <div 
          ref={trackRef} 
          className="xht-absolute xht-top-1/2 xht-h-2 xht-w-full xht-transform xht--translate-y-1/2 xht-rounded-full xht-bg-gray-200"
        >
          {/* 选中区域高亮 */}
          <div
            className="xht-absolute xht-h-full xht-rounded-full xht-bg-blue-500"
            style={{
              left: `${minPos}%`,
              width: `${maxPos - minPos}%`,
            }}
          />
        </div>

        {/* 最小值滑块和数值显示 - 仅在双滑块模式显示 */}
        {!singleMode && (
          <div className="xht-absolute xht-top-1/2 xht-transform xht--translate-y-1/2" style={{ left: `${minPos}%` }}>
            <button
              type="button"
              className={`xht-absolute xht-h-5 xht-w-5 xht-transform xht--translate-x-1/2 xht--translate-y-1/2 xht-rounded-full xht-bg-white xht-shadow-md xht-border-2 xht-border-blue-500 xht-focus:outline-none ${
                dragging === "min" ? "xht-border-blue-700 xht-shadow-lg" : ""
              }`}
              onMouseDown={handleMouseDown("min")}
              onTouchStart={handleTouchStart("min")}
              aria-label={__('设置', 'xhtheme-ai-toolbox') + minLabel}
            />
            <div className="xht-absolute xht-transform xht--translate-x-1/2 xht-mt-4 xht-flex xht-flex-col xht-items-center">
              <span className="xht-bg-blue-500 xht-text-white xht-px-2 xht-py-1 xht-rounded xht-text-xs xht-font-medium xht-min-w-[24px] xht-text-center">
                {minValue}
              </span>
            </div>
          </div>
        )}

        {/* 最大值滑块和数值显示 - 在单滑块模式下作为唯一滑块 */}
        <div className="xht-absolute xht-top-1/2 xht-transform xht--translate-y-1/2" style={{ left: `${maxPos}%` }}>
          <button
            type="button"
            className={`xht-absolute xht-h-5 xht-w-5 xht-transform xht--translate-x-1/2 xht--translate-y-1/2 xht-rounded-full xht-bg-white xht-shadow-md xht-border-2 xht-border-blue-500 xht-focus:outline-none ${
              dragging === "max" ? "xht-border-blue-500 xht-shadow-lg" : ""
            }`}
            onMouseDown={handleMouseDown("max")}
            onTouchStart={handleTouchStart("max")}
            aria-label={singleMode ? __('设置', 'xhtheme-ai-toolbox') + label : __('设置', 'xhtheme-ai-toolbox') + maxLabel}
          />
          <div className="xht-absolute xht-transform xht--translate-x-1/2 xht-mt-4 xht-flex xht-flex-col xht-items-center">
            <span className="xht-bg-blue-500 xht-text-white xht-px-2 xht-py-1 xht-rounded xht-text-xs xht-font-medium xht-min-w-[24px] xht-text-center">
              {singleMode ? value : maxValue}
            </span>
          </div>
        </div>
      </div>
      
      {summaryText && (
        <div className="xht-bg-gray-50 xht-p-2 xht-rounded xht-text-xs xht-text-gray-500 xht-text-center xht-mt-2" style={{ maxWidth: '400px' }}>
          {singleMode 
            ? summaryText.replace('{value}', value) 
            : summaryText.replace('{min}', minValue).replace('{max}', maxValue)
          }
        </div>
      )}
    </div>
  );
};

export default RangeSlider;