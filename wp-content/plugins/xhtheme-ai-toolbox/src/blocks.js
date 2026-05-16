import { __, _x, sprintf } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import {
    PanelBody,
    Button,
    Spinner,
    TextControl,
    TextareaControl,
    Flex, FlexBlock,
    Card, CardHeader, CardBody,
    __experimentalHeading as Heading,
    ToolbarGroup, ToolbarButton,ToolbarDropdownMenu,
    Modal
} from '@wordpress/components';
import { useSelect, useDispatch, dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/editor';
import { BlockControls } from '@wordpress/block-editor';
import { useState, useEffect,useRef } from '@wordpress/element';
import { count as wordCount } from '@wordpress/wordcount';
// 导入图标组件
import { AIRewriteIcon } from './components/icons';
import { blockI18n } from './components/blockI18n';

const INITIAL_STATE = {
    isLoading: false,
    buttonText: blockI18n.startAnalysis,
    apiresponse: false,
    dataPassok: false,
    description: '',
    tags: [],
    isExpanded: {},
    dataerror: '',
    apiMsgtip : '',
    selectedModel: xhthemeAiToolboxBlock?.models?.post || 'auto'
};

// AI 分析功能
const AiAnalyzer = () => {
    const postContent = useSelect(select => 
        select('core/editor').getEditedPostContent()
    , []); // 添加依赖数组
    
    const { editPost } = useDispatch('core/editor');

    const [state, setState] = useState(INITIAL_STATE);

    const updateState = (newState) => setState(prevState => ({ ...prevState, ...newState }));

    const wordNumber = wordCount(postContent,_x( 'words', 'Word count type. Do not translate!' ));

    const analyzeContent = async () => {
        updateState({ isLoading: true, dataerror: '' }); // 显示加载动画并清除之前的错误
        
        try {
            const response = await fetch(xhthemeAiToolboxBlock.apiurl, {
                method: 'POST',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'post',
                    content: postContent,
                    apitoken: xhthemeAiToolboxBlock.apitoken,
                    stream: true,
                    version: xhthemeAiToolboxBlock.version,
                    args: xhthemeAiToolboxBlock.args?.post,
                    model: state.selectedModel
                }),
            });
    
            if (!response.ok) {   
                throw new Error(`Network request error: ${response.status}`);
            }
    
            const reader = response.body.getReader();
            let newState = {};
            
            while (true) {    
                const { done, value } = await reader.read();
                if (done) break;

                let setdeltaState = {};

                const chunk = new TextDecoder().decode(value);
                const lines = chunk.split('\n\n');
    
                for (const line of lines) {    
                    const trimmedLine = line.trim();
                    if (!trimmedLine) continue;
                    
                    const jsonString = trimmedLine.startsWith('data: ') ? trimmedLine.substring(6) : trimmedLine;
                    if (!jsonString || jsonString === '') continue;
                    if (jsonString === "[DONE]") break;

                    let data;
                    try {
                        data = JSON.parse(jsonString);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError, 'Raw data:', jsonString);
                        continue;
                    }
                    
                    if (data.code === 0 && data.delta) {
                        if (data.delta.type == 'stop') {
                            setdeltaState.apiMsgtip = data.message;  
                            setdeltaState.buttonText = blockI18n.reanalyzeWithAI;  
                            setdeltaState.isLoading = false;  
                            updateState(setdeltaState);
                            break;
                        }
                        
                        if (data.delta.content) {
                            try {
                                const deltaJson = JSON.parse(data.delta.content);                                
                                if (deltaJson?.description && !newState?.description ) {
                                    newState.description = deltaJson.description;
                                    setdeltaState.description = deltaJson.description;
                                    setdeltaState.apiresponse = true;                                   
                                }
                                
                                if (deltaJson?.tags) {
                                    if (!newState.tags) {
                                        newState.tags = [];
                                    }

                                    for (const tag of deltaJson.tags) {
                                        const existingTagIndex = newState.tags.findIndex(t => t.name === tag.name);                                        
                                        if (existingTagIndex === -1) {
                                            newState.tags.push(tag);
                                            try {                                         
                                                const response = await apiFetch({
                                                    path: '/xhthemeai/v1/checktag',
                                                    method: 'POST',
                                                    data: { name: tag.name }
                                                });
                                                if (response.success) {
                                                    tag.type = response.data.exists? 'exists' : 'new';
                                                    if (!state.tags.some(t => t.name === tag.name)) {
                                                        state.isExpanded = {
                                                            ...state.isExpanded,
                                                            [tag.name]: xhthemeAiToolboxBlock.tagExpand
                                                        };
                                                        state.tags.push(tag);
                                                        setdeltaState.apiresponse = true;
                                                    }
                                                }
                                            } catch (error) {
                                                console.error('Tag check failed:', error, tag);
                                            }
                                        }
                                    }
                                }
                                
                                if (Object.keys(setdeltaState).length > 0) {
                                    updateState(setdeltaState);
                                }
                            } catch (parseError) {
                                console.error('Content parse error:', parseError, 'Raw data:', data.delta.content);
                            }
                        }
                    } else if (data.message || data.msg) {
                        throw new Error(data.message || data.msg);
                    } else {
                        throw new Error(blockI18n.error20001);
                    }
                }
            }
        } catch (error) {
            console.error('AI analysis failed:', error);
            updateState({ 
                dataerror: blockI18n.aiAnalysisFailed(error.message || blockI18n.unknownError)
            });
        } finally {
            updateState({ 
                buttonText: blockI18n.reanalyzeWithAI, 
                isLoading: false 
            });
        }
    };

    const dataUpdate = async () => {
        updateState({ isLoading: true });
        try {
            const response = await apiFetch({
                path: '/xhthemeai/v1/postupdate',
                method: 'POST',
                data: {
                    description: state.description,
                    tags: state.tags
                }
            });
            
            if (response.success) {
                editPost(response.data);
                updateState({ dataPassok: true });
                dispatch('core/editor').savePost();
                setTimeout(() => {
                    dispatch('core/edit-post').openGeneralSidebar('edit-post/document');
                    updateState({ apiresponse: false, dataPassok: false });                    
                }, 1500);
            }
        } catch (error) {
            console.error('Information update failed:', error);
            updateState({ dataerror: blockI18n.informationUpdateFailed });
        } finally {
            updateState({ buttonText: blockI18n.reanalyzeWithAI, isLoading: false });
        }
    }; 

    const removeTag = (tagToRemove) => {
        updateState({ tags: state.tags.filter(tag => tag !== tagToRemove) });
    };

    const toggleCardBody = (tagName) => {
        updateState({ 
            isExpanded: {
                ...state.isExpanded,
                [tagName]: !state.isExpanded[tagName]
            }
        });
    };

    return (
        <>
            <PluginSidebar
                name="ai-analyzer"
                title={blockI18n.aiIntelligentCompletion}
                icon={ AIRewriteIcon.ai }
                >
                <PanelBody>
                    { state.apiresponse ? (
                        <>
                        { state.apiMsgtip && (
                            <p style={{
                                color:'#009688',
                                opacity: 1,
                                transition: 'opacity 0.5s ease-in-out',
                                animation: 'fadeIn 0.5s ease-in-out'
                                }}
                                dangerouslySetInnerHTML={{ __html: state.apiMsgtip }}
                            ></p>
                        )}
                        
                        { state.description && (
                            <TextareaControl
                                label={blockI18n.articleSummary}
                                value={state.description}
                                onChange={(value) => updateState({ description: value })}
                            />
                        )}
                        { state.tags && state.tags.length > 0 && (
                            <>
                            <Heading 
                                level='3'
                                style={{
                                    marginTop:'1.5rem'
                                }}
                            >
                                {blockI18n.tagLabels}
                            </Heading>
                            <Flex direction='column'>
                                {state.tags.map((tag, index) => (
                                    <FlexBlock key={index}>
                                        <Card size='xSmall'>
                                            <CardHeader>
                                            <Flex justify="space-between" align="center">
                                                <span style={{fontWeight:'bold', marginLeft:'5px'}}>
                                                    {tag.name} 
                                                    { tag.type === 'new' ? (
                                                        <span style={{color:'#FF5722', fontWeight:'normal', marginLeft:'5px'}}>{blockI18n.newTag}</span>
                                                    ) : (
                                                        <span style={{color:'#009688', fontWeight:'normal', marginLeft:'5px',transform:'scale(.85)',display: 'inline-block'}}>{blockI18n.existingTag}</span>
                                                    )}                                                   
                                                </span>
                                                <Flex justify="flex-end" style={{width:'auto'}}>
                                                    { tag.type === 'new' && (
                                                        <Button
                                                            size='small'
                                                            onClick={() => toggleCardBody(tag.name)}
                                                            >
                                                            {state.isExpanded[tag.name] ? (
                                                                <i class="cxicon cxicon-chevron-up me-0"></i>
                                                            ) : (
                                                                <i class="cxicon cxicon-chevron-down me-0"></i>
                                                            )}
                                                        </Button>
                                                    )}
                                                    
                                                    <Button
                                                        size='small'
                                                        isDestructive
                                                        onClick={() => removeTag(tag)}
                                                        style={{
                                                            paddingLeft : '3px',
                                                            paddingRight : '3px'
                                                        }}
                                                    >
                                                        <i class="cxicon cxicon-error me-0"></i>
                                                    </Button>
                                                </Flex>
                                            </Flex>
                                            </CardHeader>
                                            {
                                                tag.type === 'new' && state.isExpanded[tag.name] &&
                                                <CardBody>
                                                    <TextControl
                                                        label={blockI18n.tagAlias}
                                                        value={tag.slug || ''}
                                                        onChange={(value) => updateState({ tags: state.tags.map(t => t === tag ? { ...t, slug: value } : t) })}
                                                    />

                                                    <TextControl
                                                        label={blockI18n.seoTitle}
                                                        value={tag.title || ''}
                                                        onChange={(value) => updateState({ tags: state.tags.map(t => t === tag ? { ...t, title: value } : t) })}
                                                    />

                                                    <TextareaControl
                                                        label={blockI18n.tagDescription}
                                                        value={tag.description || ''}
                                                        onChange={(value) => updateState({ tags: state.tags.map(t => t === tag ? { ...t, description: value } : t) })}
                                                    />
                                                </CardBody>
                                            }
                                        </Card>
                                    </FlexBlock>
                                ))}
                            </Flex>
                        </>                            
                        )}
                        
                        <p style={{
                            marginTop:'1.5rem',
                            textAlign : 'right'
                            }}>
                            {state.isLoading ? (
                                <Flex justify='center' direction='column' align="center" style={{ marginTop: '30px' }}>
                                    <Spinner style={{transform: 'scale(1.5)'}} />
                                    <p style={{opacity:'.75',marginTop:'10px'}}>{blockI18n.processingPleaseWait}</p>
                                </Flex>
                            ) : (
                                state.dataPassok ? (
                                    <p
                                        style={{
                                            color:'#009688',
                                            marginTop:'1.5rem',
                                            textAlign:'center',
                                            fontWeight:'bold'
                                        }}
                                    >
                                        {blockI18n.processComplete}
                                    </p>
                                ) : (
                                    <>
                                        <Button
                                            variant="primary"
                                            onClick={dataUpdate}
                                            className='xh-grop-botton'
                                            style={{
                                                width:'100%',
                                                textAlign:'center',
                                                justifyContent:'center',
                                            }}
                                        >
                                            {blockI18n.aiOneClickApply}
                                        </Button>
                                        <Button
                                            variant="link"
                                            onClick={() => updateState({ 
                                                apiresponse: false,
                                                buttonText: blockI18n.reanalyzeWithAI
                                            })}
                                            className='xh-grop-botton'
                                            style={{
                                                marginTop: '20px'
                                            }}
                                        >
                                            {blockI18n.returnToReanalyze}
                                        </Button>
                                    </>
                                )                               
                            )}
                        </p>
                    </>
                    ) : (
                        <>
                            <p>
                                {blockI18n.wordCountMessage(wordNumber)}
                            </p>
                            {state.isLoading ? (
                                <Flex justify='center' direction='column' align="center" style={{ marginTop: '40px' }}>
                                    <Spinner style={{transform: 'scale(1.5)'}} />
                                    <p style={{opacity:'.75',marginTop:'10px'}}>{blockI18n.analyzingPleaseWait}</p>
                                </Flex>
                                
                            ) : (
                                <>
                                    { state.dataerror && (
                                        <p
                                        style={{
                                            color:'#FF5722',
                                            marginTop:'1.5rem',
                                            textAlign:'center',
                                            fontWeight:'bold'
                                        }}
                                        >{state.dataerror}</p>
                                    )}

                                    <Button
                                        variant="primary"
                                        disabled={ wordNumber < 300 }
                                        onClick={analyzeContent}
                                        className='xh-grop-botton'
                                        style={{
                                            width:'100%',
                                            textAlign:'center',
                                            justifyContent:'center',
                                        }}
                                        >
                                        {state.buttonText}
                                    </Button>

                                    { /** 模型选择 */ }
                                    <div style={{ marginTop: '1rem', display: 'flex', alignItems: 'center' }}>
                                        <span style={{ opacity: '.75' }}>{blockI18n.model}</span>
                                        <ToolbarDropdownMenu
                                            icon={
                                                <Flex align="center" style={{ height: '24px',color:'#007cba'}} gap="1">
                                                    {AIRewriteIcon.model}
                                                    <span style={{ lineHeight: '24px' }}>
                                                        {xhthemeAiToolboxBlock.chatModels.find(m => m.value === state.selectedModel)?.label || 'Auto'}
                                                    </span>
                                                </Flex>
                                            }
                                            label={blockI18n.clickToSwitchModel}
                                            className='xh-grop-botton'
                                            controls={xhthemeAiToolboxBlock.chatModels.map(model => ({
                                                icon: AIRewriteIcon.model2,
                                                title: model.label,
                                                isActive: state.selectedModel === model.value,
                                                onClick: () => updateState({ selectedModel: model.value }),
                                            }))}
                                        />
                                    </div>
                                </>
                            )}
                        </>                            
                    )}
                </PanelBody>
            </PluginSidebar>
        </>
    );
};

// 注册插件
if( xhthemeAiToolboxBlock?.models?.post !== 'none' ){
    registerPlugin('meteor-ai-analyzer', {
        render: AiAnalyzer
    });
}

/**
 * 段落重写
 */
const blockStates = new Map();

const AIRewriteButton = (BlockEdit) => (props) => {
    const { name, isSelected, attributes, setAttributes, clientId } = props;
    if (!isSelected || name !== 'core/paragraph') {
        return (
            <BlockEdit {...props} />
        );
    }

    const stopRef = useRef(false);

    // 从 Map 中获取已存储的状态，如果没有则使用初始状态
    const [aiState, setAiState] = useState(() => {
        return blockStates.get(clientId) || {
            isActive: false,
            originalContent: '',
            canRestore: false,
            modelMessage: '',
            shouldStop: false,
            selectedModel: xhthemeAiToolboxBlock?.models?.paragraph || 'auto',
            isLoading: false,
            isModalOpen: false,
        };
    });

    // 更新状态时同时更新 Map 中的存储
    const updateAiState = (newState) => {
        setAiState(prevState => {
            const updatedState = { ...prevState, ...newState };
            blockStates.set(clientId, updatedState);
            return updatedState;
        });
    };

    // 组件卸载时清理该块的状态
    useEffect(() => {
        return () => {
            if (!aiState.isActive) {
                blockStates.delete(clientId);
            }
        };
    }, [clientId, aiState.isActive]);

    const handleAIOptionSelect = async (option) => {
        // 参数验证
        if (!option || !attributes?.content) {
            console.error('Invalid parameters');
            return;
        }
    
        // 验证必要的API配置
        if (!xhthemeAiToolboxBlock?.apiurl || !xhthemeAiToolboxBlock?.apitoken) {
            console.error('API configuration is missing');
            updateAiState({
                isActive: false,
                isLoading: false,
                modelMessage: blockI18n.systemConfigError
            });
            return;
        }
    
        // 验证内容
        if (!attributes?.content) {
            console.error('Content is empty');
            updateAiState({
                isActive: false,
                isLoading: false,
                modelMessage: blockI18n.enterContentFirst
            });
            return;
        }

        // 先保存原始内容
        const originalContent = attributes.content;
        stopRef.current = false;

        updateAiState({
            isLoading: true,
            isActive: true,
            originalContent: originalContent,
            canRestore: false,
            modelMessage: '',
            shouldStop: false
        });

        let result = '';    
        try {
            const response = await fetch(xhthemeAiToolboxBlock.apiurl, {
                method: 'POST',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'paragraph',
                    content: attributes.content,
                    apitoken: xhthemeAiToolboxBlock.apitoken,
                    stream: true,
                    version: xhthemeAiToolboxBlock.version,
                    args: {
                        type: option,
                        length: 1
                    },
                    model: aiState.selectedModel
                }),
            });
    
            if (!response.ok) {   
                throw new Error(`Network response error: ${response.status}`);
            }
    
            updateAiState({
                isLoading: false
            });
    
            const reader = response.body.getReader();
    
            while (true) {
                if (stopRef.current) {
                    updateAiState({
                        modelMessage: __('User manually terminated the request!', 'xhtheme-ai-toolbox'),
                        isActive: false,
                        canRestore: true
                    });
                    break;
                }
    
                const { done, value } = await reader.read();
                if (done) break;
    
                const chunk = new TextDecoder().decode(value);
                const lines = chunk.split('\n\n');
    
                for (const line of lines) {
                    if (stopRef.current) break;
    
                    try {
                        const trimmedLine = line.trim();
                        const jsonString = trimmedLine.startsWith('data: ') ? trimmedLine.substring(6) : trimmedLine;
                        // 添加空字符串检查
                        if (!jsonString || jsonString === '') {
                            continue;
                        }
                        if (jsonString === "[DONE]") {
                            updateAiState({
                                isActive: false,
                                canRestore: true,
                                shouldStop: true
                            });
                            continue;
                        }

                        // 添加 try-catch 专门处理 JSON 解析
                        let data;
                        try {
                            data = JSON.parse(jsonString);
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError, 'Raw data:', jsonString);
                            continue; // 跳过无效的 JSON 数据
                        }
                        
                        if (data.code == 0 && data.delta) {
                            if (data.delta.type == 'stop') {
                                updateAiState({
                                    modelMessage: data.message
                                });
                            }
                    
                            if (data.delta.content) {
                                result += data.delta.content;
                                setAttributes({ content: result });
                            }
                        } else if (data.message) {
                            throw new Error(data.message);
                        } else if (data.msg) {
                            throw new Error(data.msg);
                        } else {
                            throw new Error( __('[error:20001]Request error, please try again later!', 'xhtheme-ai-toolbox') );
                        }
                    } catch (error) {
                        console.error('Error processing response:', error);
                        result = `<span style="color:#f66">${error.message + __('[Content will be restored automatically in 2 seconds]', 'xhtheme-ai-toolbox')}</span>`;
                        setAttributes({ content: result });
                        
                        setTimeout(() => {
                            setAttributes({ content: originalContent });
                            updateAiState({ 
                                isActive: false,
                                canRestore: false
                            });
                        }, 2000);
                        throw error; // 向上传播错误
                    }
                }
            }
        } catch (error) {
            console.error('Error during AI processing:', error);
            updateAiState({
                isActive: false,
                isLoading: false,
                modelMessage: error.message
            });
            if (aiState.originalContent) {
                updateAiState({
                    canRestore: true
                });
            }
        } finally {
            // 确保状态正确清理
            if (!stopRef.current) {
                updateAiState({
                    isLoading: false
                });
            }
        }
    };

    const handleAIButtonClick = () => {
        if (aiState.isActive) {
            stopRef.current = true;
            updateAiState({
                isActive: false,
                canRestore: true,
                shouldStop: true
            });
        }
    };

    const handleRestoreClick = () => {
        if (!aiState.canRestore) return;
        props.setAttributes({ content: aiState.originalContent });
        updateAiState({ canRestore: false });
    };

    return (
        <>
            <BlockEdit {...props} />
            <BlockControls group="other">
                <ToolbarGroup>
                    {aiState.isActive ? (
                        aiState.isLoading ? (
                            <ToolbarButton
                                icon={<Spinner />}
                                label={blockI18n.loading}
                            />
                        ) : (
                            <ToolbarButton
                                icon={AIRewriteIcon.stop}
                                label={blockI18n.stopOutput}
                                onClick={handleAIButtonClick}
                            />
                        )
                    ) : (
                        <ToolbarDropdownMenu
                            icon={AIRewriteIcon.ai}
                            label={blockI18n.startAI}
                            controls={[
                                {
                                    title: blockI18n.paragraphContentOptimization,
                                    icon: AIRewriteIcon.optimize,
                                    onClick: () => handleAIOptionSelect('1'),
                                },
                                {
                                    title: blockI18n.writeBasedOnContent,
                                    icon: AIRewriteIcon.write,
                                    onClick: () => handleAIOptionSelect('2'),
                                },
                            ]}
                        />
                    )}

                    {aiState.canRestore && (
                        <ToolbarButton
                            icon={AIRewriteIcon.restore}
                            label={blockI18n.restoreContent}
                            onClick={handleRestoreClick}
                            className="toolbar-button-fadein"
                        />                    
                    )}

                    <ToolbarDropdownMenu
                        icon={AIRewriteIcon.model}
                        label={blockI18n.modelLabel(
                            xhthemeAiToolboxBlock.chatModels.find(m => m.value === aiState.selectedModel)?.label || blockI18n.autoModel
                        )}
                        controls={xhthemeAiToolboxBlock.chatModels.map(model => ({
                            icon : AIRewriteIcon.model2,
                            title: model.label,
                            isActive: aiState.selectedModel === model.value,
                            onClick: () => updateAiState({ selectedModel: model.value }),
                        }))}
                    />
                    {aiState.modelMessage && (
                        <>
                            <ToolbarButton
                                icon={AIRewriteIcon.message}
                                label={blockI18n.aiReturnInformation}
                                className="toolbar-button-fadein"
                                onClick={() => updateAiState({ isModalOpen: true }) }
                            />
                            {aiState.isModalOpen && (
                                <Modal
                                    title={blockI18n.aiReturnInformation}
                                    onRequestClose={() => updateAiState({ isModalOpen: false })}
                                    style={{
                                        maxWidth: '500px',
                                        margin: 'auto'
                                    }}
                                    >
                                    <p dangerouslySetInnerHTML={{ __html: aiState.modelMessage }}></p>
                                </Modal>
                            )}
                        </>
                    )}
                </ToolbarGroup>
            </BlockControls>
        </>
    );
};

addFilter(
    'editor.BlockEdit',
    'xhtheme/paragraph',
    AIRewriteButton
);
