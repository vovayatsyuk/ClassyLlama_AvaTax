<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @author      Matt Johnson <matt.johnson@classyllama.com>
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Log;

use ClassyLlama\AvaTax\Controller\Adminhtml\Log;
use ClassyLlama\AvaTax\Model\LogFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Support\Model\DataFormatter;
use Magento\Framework\Exception\LocalizedException;

/**
 * View log
 */
class View extends Log
{
    /**
     * @var \ClassyLlama\AvaTax\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Support\Model\DataFormatter
     */
    protected $dataFormatter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \ClassyLlama\AvaTax\Model\LogFactory $logFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Support\Model\DataFormatter $dataFormatter
     */
    public function __construct(
        Context $context,
        LogFactory $logFactory,
        Registry $coreRegistry,
        DataFormatter $dataFormatter
    ) {
        $this->logFactory = $logFactory;
        $this->coreRegistry = $coreRegistry;
        $this->dataFormatter = $dataFormatter;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var \ClassyLlama\AvaTax\Model\Log $model */
            $model = $this->initLog();
            if (!$model->getId()) {
                $this->messageManager->addError(__('Requested log no longer exists.'));

                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }

            $dateString = $model->getCreatedAt();

            /** @var Page $pageResult */
            $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $pageResult->setActiveMenu('ClassyLlama_AvaTax::avatax_log');
            $pageResult->getConfig()->getTitle()->prepend(
                $dateString . ' ' . $this->dataFormatter->getSinceTimeString($dateString)
            );
            return $pageResult;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to read log data to display.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $redirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Load system report from request
     *
     * @param string $idFieldName
     * @return \ClassyLlama\AvaTax\Model\Log $model
     */
    protected function initLog($idFieldName = 'id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);

        /** @var \ClassyLlama\AvaTax\Model\Log $model */
        $model = $this->logFactory->create();
        if ($id) {
            $model->load($id);
        }
        if (!$this->coreRegistry->registry('current_log')) {
            $this->coreRegistry->register('current_log', $model);
        }
        return $model;
    }
}
