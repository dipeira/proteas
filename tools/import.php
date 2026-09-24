<?php
  require_once "../config.php";
  require_once '../include/functions.php';
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Εισαγωγή δεδομένων από αρχείο';
    require '../etc/head.php'; 
    ?>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <style>
      :root {
        --primary: #0284c7;
        --primary-hover: #0369a1;
        --primary-light: #f0f9ff;
        --primary-border: #bae6fd;
        --slate-900: #0f172a;
        --slate-800: #1e293b;
        --slate-700: #334155;
        --slate-600: #475569;
        --slate-500: #64748b;
        --slate-400: #94a3b8;
        --slate-300: #cbd5e1;
        --slate-200: #e2e8f0;
        --slate-100: #f1f5f9;
        --slate-50: #f8fafc;
        --success: #10b981;
        --success-light: #ecfdf5;
        --success-border: #a7f3d0;
        --warning: #f59e0b;
        --warning-light: #fffbeb;
        --warning-border: #fde68a;
        --danger: #ef4444;
        --danger-light: #fef2f2;
        --danger-border: #fecaca;
        --purple: #8b5cf6;
        --purple-light: #f5f3ff;
        --purple-border: #ddd6fe;
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
      }

      body {
        background-color: #f1f5f9;
        color: var(--slate-800);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      }

      .import-page-wrapper {
        max-width: 1240px;
        margin: 20px auto 60px auto;
        padding: 0 16px;
      }

      /* Header Card (Clean & High Contrast) */
      .import-hero {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-top: 4px solid var(--primary);
        border-radius: var(--radius-lg);
        padding: 28px 32px;
        box-shadow: 0 4px 14px -2px rgba(15, 23, 42, 0.05);
        margin-bottom: 24px;
        position: relative;
      }

      .hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #0369a1;
        margin-bottom: 12px;
        border: 1px solid #bfdbfe;
      }

      .import-hero h1 {
        font-size: 26px;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
      }

      .import-hero h1 svg {
        color: var(--primary);
      }

      .import-hero p {
        color: #475569;
        font-size: 14.5px;
        max-width: 860px;
        margin: 0 0 18px 0;
        line-height: 1.6;
      }

      .hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .hero-badge-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px solid var(--slate-200);
        padding: 5px 12px;
        border-radius: var(--radius-sm);
        font-size: 12.5px;
        color: var(--slate-700);
      }

      /* Workflow Steps Bar */
      .steps-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 24px;
      }

      @media (max-width: 768px) {
        .steps-bar {
          grid-template-columns: 1fr;
        }
      }

      .step-item {
        background: #ffffff;
        border: 1.5px solid var(--slate-200);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
      }

      .step-item.active {
        border-color: var(--primary);
        background: #f0f9ff;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1);
      }

      .step-item.completed {
        border-color: var(--success);
        background: #f0fdf4;
      }

      .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--slate-200);
        color: var(--slate-700);
        font-weight: 700;
        font-size: 13.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
      }

      .step-item.active .step-circle {
        background: var(--primary);
        color: #ffffff;
      }

      .step-item.completed .step-circle {
        background: var(--success);
        color: #ffffff;
      }

      .step-content {
        display: flex;
        flex-direction: column;
      }

      .step-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--slate-900);
      }

      .step-subtitle {
        font-size: 12px;
        color: var(--slate-500);
      }

      /* Toolbar & Search */
      .import-toolbar {
        background: #ffffff;
        border-radius: var(--radius-md);
        padding: 14px 18px;
        border: 1px solid var(--slate-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
      }

      @media (min-width: 900px) {
        .import-toolbar {
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
        }
      }

      .category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
      }

      .cat-tab {
        border: none;
        background: var(--slate-100);
        color: var(--slate-600);
        font-size: 13px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }

      .cat-tab:hover {
        background: var(--slate-200);
        color: var(--slate-900);
      }

      .cat-tab.active {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
      }

      .cat-tab .tab-badge {
        background: rgba(0, 0, 0, 0.08);
        padding: 1px 7px;
        border-radius: 9999px;
        font-size: 11px;
      }

      .cat-tab.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
      }

      .search-box-wrapper {
        position: relative;
        min-width: 260px;
        flex-grow: 1;
        max-width: 420px;
      }

      .search-input {
        width: 100%;
        padding: 9px 36px 9px 36px;
        border: 1.5px solid var(--slate-300);
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        background: var(--slate-50);
        color: var(--slate-900);
        outline: none;
        transition: all 0.2s ease;
        box-sizing: border-box;
      }

      .search-input:focus {
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
      }

      .search-icon-svg {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        fill: none;
        stroke: var(--slate-400);
        stroke-width: 2;
        pointer-events: none;
      }

      .clear-search-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: var(--slate-400);
        font-size: 14px;
        cursor: pointer;
        padding: 4px;
        display: none;
      }

      .clear-search-btn:hover {
        color: var(--slate-800);
      }

      /* Category Groups */
      .category-group {
        margin-bottom: 28px;
        transition: all 0.2s ease;
      }

      .group-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--slate-200);
      }

      .group-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--slate-900);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
      }

      .group-desc {
        font-size: 12.5px;
        color: var(--slate-500);
        margin: 0;
      }

      .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
      }

      /* Option Card */
      .option-card {
        position: relative;
        background: #ffffff;
        border: 1.5px solid var(--slate-200);
        border-radius: var(--radius-md);
        padding: 18px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: var(--shadow-sm);
      }

      .option-card:hover {
        border-color: #38bdf8;
        box-shadow: 0 8px 18px -4px rgba(2, 132, 199, 0.12);
        transform: translateY(-2px);
      }

      .option-card.selected {
        border-color: var(--primary);
        background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
        box-shadow: 0 0 0 2px var(--primary), 0 8px 20px -4px rgba(2, 132, 199, 0.18);
      }

      .option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
      }

      .card-header-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
      }

      .card-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }

      .icon-blue { background: #e0f2fe; color: #0284c7; }
      .icon-indigo { background: #e0e7ff; color: #4338ca; }
      .icon-emerald { background: #d1fae5; color: #059669; }
      .icon-amber { background: #fef3c7; color: #d97706; }
      .icon-purple { background: #f3e8ff; color: #7e22ce; }

      .card-heading-box {
        flex-grow: 1;
      }

      .card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--slate-900);
        margin: 0 0 4px 0;
        line-height: 1.35;
      }

      .card-desc {
        font-size: 12.5px;
        color: var(--slate-600);
        margin: 0;
        line-height: 1.45;
      }

      .card-check-bubble {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid var(--slate-300);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
        background: #ffffff;
      }

      .option-card.selected .card-check-bubble {
        border-color: var(--primary);
        background: var(--primary);
        color: #ffffff;
      }

      .card-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
      }

      .chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2.5px 8px;
        border-radius: var(--radius-sm);
        font-size: 11.5px;
        font-weight: 600;
        background: var(--slate-100);
        color: var(--slate-600);
      }

      .chip-warning {
        background: var(--warning-light);
        color: #92400e;
        border: 1px solid var(--warning-border);
      }

      .chip-accent {
        background: var(--primary-light);
        color: var(--primary-hover);
        border: 1px solid var(--primary-border);
      }

      .chip-purple {
        background: var(--purple-light);
        color: #6b21a8;
        border: 1px solid var(--purple-border);
      }

      .card-footer-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid var(--slate-100);
        gap: 8px;
      }

      .btn-sample {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-light);
        border: 1px solid var(--primary-border);
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
      }

      .btn-sample:hover {
        background: var(--primary);
        color: #ffffff;
        text-decoration: none;
      }

      .btn-secondary-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        color: var(--slate-700);
        background: var(--slate-100);
        border: 1px solid var(--slate-300);
        padding: 5px 9px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
      }

      .btn-secondary-link:hover {
        background: var(--slate-200);
        color: var(--slate-900);
        text-decoration: none;
      }

      .btn-wizard {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: #7e22ce;
        background: var(--purple-light);
        border: 1px solid var(--purple-border);
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
      }

      .btn-wizard:hover {
        background: #7e22ce;
        color: #ffffff;
        text-decoration: none;
      }

      /* Step 2: Dynamic Helper Box */
      .assistant-box {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1.5px solid #7dd3fc;
        border-radius: var(--radius-md);
        padding: 20px 24px;
        margin: 26px 0;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);
        transition: all 0.3s ease;
      }

      .assistant-box.empty {
        background: var(--slate-50);
        border: 1.5px dashed var(--slate-300);
        box-shadow: none;
      }

      .assistant-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 10px;
      }

      .assistant-title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--primary-hover);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
      }

      .assistant-box.empty .assistant-title {
        color: var(--slate-600);
      }

      .assistant-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #bae6fd;
      }

      .detail-card {
        background: rgba(255, 255, 255, 0.8);
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.9);
      }

      .detail-label {
        font-size: 11px;
        color: var(--slate-500);
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 2px;
      }

      .detail-val {
        font-size: 13px;
        font-weight: 700;
        color: var(--slate-900);
      }

      .assistant-warning {
        background: #fef3c7;
        border-left: 4px solid var(--warning);
        padding: 10px 14px;
        border-radius: 6px;
        margin-top: 12px;
        font-size: 13px;
        color: #92400e;
      }

      /* Step 3: File Upload Dropzone */
      .upload-section-card {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        padding: 24px;
        margin: 26px 0;
        box-shadow: var(--shadow-sm);
      }

      .upload-section-header {
        margin-bottom: 16px;
      }

      .upload-section-header h3 {
        font-size: 16.5px;
        font-weight: 700;
        color: var(--slate-900);
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .upload-section-header p {
        font-size: 13px;
        color: var(--slate-500);
        margin: 0;
      }

      .dropzone-container {
        border: 2px dashed var(--slate-300);
        border-radius: var(--radius-md);
        background: var(--slate-50);
        padding: 34px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
      }

      .dropzone-container:hover, .dropzone-container.dragover {
        border-color: var(--primary);
        background: #f0f9ff;
      }

      .dropzone-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 12px auto;
        border-radius: 50%;
        background: #e0f2fe;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .dropzone-main-text {
        font-size: 15px;
        font-weight: 700;
        color: var(--slate-800);
        margin-bottom: 4px;
      }

      .dropzone-sub-text {
        font-size: 12.5px;
        color: var(--slate-500);
        margin-bottom: 12px;
      }

      .btn-browse-file {
        display: inline-block;
        background: var(--primary);
        color: #ffffff;
        font-weight: 600;
        font-size: 13px;
        padding: 7px 18px;
        border-radius: 6px;
        transition: all 0.15s ease;
      }

      .btn-browse-file:hover {
        background: var(--primary-hover);
      }

      /* Selected File Preview Box */
      .file-preview-card {
        display: none;
        background: #ffffff;
        border: 1.5px solid var(--success);
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        margin-top: 14px;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
        transition: all 0.2s ease;
      }

      .file-preview-card.status-error {
        border-color: var(--danger) !important;
        background: #fffafa !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.12) !important;
      }

      .file-preview-card.status-error .file-icon-badge {
        background: #fee2e2 !important;
        color: var(--danger) !important;
      }

      .file-preview-card.status-success {
        border-color: var(--success) !important;
        background: #f0fdf4 !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.12) !important;
      }

      .file-preview-card.status-success .file-icon-badge {
        background: #d1fae5 !important;
        color: var(--success) !important;
      }

      .file-preview-card.status-info {
        border-color: var(--primary) !important;
        background: #f8fafc !important;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.1) !important;
      }

      .file-preview-card.status-info .file-icon-badge {
        background: #e0f2fe !important;
        color: var(--primary) !important;
      }

      .file-preview-details {
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .file-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #d1fae5;
        color: var(--success);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        transition: all 0.2s ease;
      }

      .file-name-text {
        font-weight: 700;
        color: var(--slate-900);
        font-size: 14px;
      }

      .file-size-text {
        font-size: 12px;
        color: var(--slate-500);
      }

      .btn-remove-file {
        background: none;
        border: 1px solid var(--slate-300);
        padding: 5px 10px;
        border-radius: 5px;
        color: var(--danger);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
      }

      .btn-remove-file:hover {
        background: var(--danger-light);
        border-color: var(--danger-border);
      }

      /* Column Validation Feedback Card */
      .validation-feedback-card {
        margin-top: 14px;
        padding: 16px 20px;
        border-radius: var(--radius-sm);
        display: flex;
        flex-direction: column;
        gap: 12px;
        animation: fadeInValidation 0.25s ease-out;
      }

      @keyframes fadeInValidation {
        from {
          opacity: 0;
          transform: translateY(-6px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .validation-feedback-card.status-success {
        background: #f0fdf4;
        border: 1.5px solid #10b981;
        color: #065f46;
      }

      .validation-feedback-card.status-error {
        background: #fef2f2;
        border: 1.5px solid #ef4444;
        color: #991b1b;
      }

      .validation-feedback-card.status-info {
        background: #f0f9ff;
        border: 1.5px solid #0284c7;
        color: #0c4a6e;
      }

      .validation-feedback-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
      }

      .validation-status-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .status-success .validation-status-icon {
        background: #d1fae5;
        color: #059669;
      }

      .status-error .validation-status-icon {
        background: #fee2e2;
        color: #dc2626;
      }

      .status-info .validation-status-icon {
        background: #e0f2fe;
        color: #0284c7;
      }

      .validation-status-body {
        flex-grow: 1;
      }

      .validation-status-title {
        margin: 0 0 5px 0;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .status-success .validation-status-title {
        color: #065f46;
      }

      .status-error .validation-status-title {
        color: #991b1b;
      }

      .status-info .validation-status-title {
        color: #075985;
      }

      .validation-status-desc {
        margin: 0;
        font-size: 13.5px;
        line-height: 1.6;
      }

      .validation-col-badge {
        display: inline-block;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 13px;
        letter-spacing: -0.2px;
      }

      .validation-col-badge.detected-err {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #f87171;
      }

      .validation-col-badge.detected-ok {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
      }

      .validation-col-badge.expected {
        background: #ffffff;
        color: var(--slate-800);
        border: 1px solid var(--slate-300);
      }

      .validation-subnote {
        margin-top: 8px;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 5px;
        font-size: 12.5px;
        line-height: 1.5;
        border-left: 3.5px solid currentColor;
      }

      .validation-detected-headers {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed rgba(0, 0, 0, 0.12);
      }

      .detected-label {
        font-size: 12px;
        font-weight: 700;
        opacity: 0.85;
        display: block;
        margin-bottom: 6px;
      }

      .detected-tags-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
      }

      .header-tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.15);
        color: var(--slate-700);
        font-size: 11.5px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
      }

      .header-tag-pill .pill-num {
        font-weight: 700;
        opacity: 0.55;
        font-size: 10px;
      }

      .validation-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 6px;
        padding-top: 10px;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
      }

      .btn-val-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
      }

      .btn-val-action.sample {
        background: #ffffff;
        color: #0284c7;
        border: 1px solid #bae6fd;
      }

      .btn-val-action.sample:hover {
        background: #f0f9ff;
        border-color: #0284c7;
        color: #0369a1;
      }

      .btn-val-action.reselect {
        background: #ffffff;
        color: #dc2626;
        border: 1px solid #fecaca;
      }

      .btn-val-action.reselect:hover {
        background: #fee2e2;
        border-color: #ef4444;
        color: #b91c1c;
      }

      /* Tips / Best Practices Drawer */
      .tips-accordion {
        background: var(--slate-50);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        margin-top: 16px;
        font-size: 13px;
        color: var(--slate-700);
      }

      .tips-accordion summary {
        font-weight: 700;
        color: var(--slate-800);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        outline: none;
      }

      .tips-list {
        margin: 10px 0 0 0;
        padding-left: 20px;
        line-height: 1.6;
      }

      .tips-list li {
        margin-bottom: 4px;
      }

      /* Submit Actions Bar */
      .submit-actions-bar {
        background: #ffffff;
        border-top: 1px solid var(--slate-200);
        padding: 20px 24px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
      }

      @media (min-width: 640px) {
        .submit-actions-bar {
          flex-direction: row;
        }
      }

      .btn-import-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: none;
        padding: 13px 30px;
        font-size: 15px;
        font-weight: 700;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
        min-width: 240px;
      }

      .btn-import-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
      }

      .btn-import-submit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
      }

      .btn-return-home {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--slate-100);
        color: var(--slate-700);
        border: 1px solid var(--slate-300);
        padding: 11px 20px;
        font-size: 13.5px;
        font-weight: 600;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
      }

      .btn-return-home:hover {
        background: var(--slate-200);
        color: var(--slate-900);
        text-decoration: none;
      }

      /* No Results Search State */
      .empty-search-state {
        display: none;
        background: #ffffff;
        border: 1.5px dashed var(--slate-300);
        border-radius: var(--radius-md);
        padding: 40px 20px;
        text-align: center;
        color: var(--slate-500);
      }

      .empty-search-state h4 {
        margin: 8px 0;
        color: var(--slate-800);
      }

      /* Result Alert Styles */
      .import-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
      }

      .result-message {
        padding: 22px 26px;
        border-radius: var(--radius-md);
        margin: 20px 0;
        line-height: 1.6;
      }

      .result-success {
        background: #ecfdf5;
        border-left: 5px solid var(--success);
        color: #065f46;
      }

      .result-error {
        background: #fef2f2;
        border-left: 5px solid var(--danger);
        color: #991b1b;
      }

      .result-warning {
        background: #fffbeb;
        border-left: 5px solid var(--warning);
        color: #92400e;
      }
    </style>
  </head>
  <body>

<?php
  include_once("class.login.php");
  $log = new logmein();
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
      exit;
  }
  else
      $loggedin = 1;

  // check if admin
  if ($_SESSION['userlevel'] > 0)
  {
    echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτής της ενέργειας. Επικοινωνήστε με το διαχειριστή σας.</h3>";
    die();
  }
  
  if (!isset($_POST['submit']))
  {
    require '../etc/menu.php';
?>
    <div class="import-page-wrapper">
      <!-- Hero Banner -->
      <div class="import-hero">
        <div class="hero-tag">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
          Κεντρικη Διαχειριση Δεδομενων
        </div>
        <h1>
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
          Εισαγωγή Δεδομένων στη Βάση
        </h1>
        <p>
          Επιλέξτε την κατηγορία και τον τύπο δεδομένων που επιθυμείτε να εισάγετε ή να ενημερώσετε μαζικά. 
          Κατεβάστε το αντίστοιχο πρότυπο αρχείο CSV, συμπληρώστε τα στοιχεία σας και εκτελέστε την εισαγωγή με ασφάλεια.
        </p>
        <div class="hero-badges">
          <div class="hero-badge-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Αρχεία CSV με διαχωριστικό ελληνικό ερωτηματικό (;)
          </div>
          <div class="hero-badge-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Κωδικοποίηση UTF-8
          </div>
          <div class="hero-badge-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Αυτόματη παράλειψη γραμμής επικεφαλίδων
          </div>
        </div>
      </div>

      <!-- Step Navigation Bar -->
      <div class="steps-bar">
        <div class="step-item active" id="stepIndicator1">
          <div class="step-circle">1</div>
          <div class="step-content">
            <span class="step-title">Βήμα 1: Επιλογή Ενέργειας</span>
            <span class="step-subtitle">Επιλέξτε τον τύπο δεδομένων</span>
          </div>
        </div>
        <div class="step-item" id="stepIndicator2">
          <div class="step-circle">2</div>
          <div class="step-content">
            <span class="step-title">Βήμα 2: Πρότυπο & Οδηγίες</span>
            <span class="step-subtitle">Λήψη CSV & προδιαγραφές</span>
          </div>
        </div>
        <div class="step-item" id="stepIndicator3">
          <div class="step-circle">3</div>
          <div class="step-content">
            <span class="step-title">Βήμα 3: Μεταφόρτωση</span>
            <span class="step-subtitle">Επιλογή αρχείου & εισαγωγή</span>
          </div>
        </div>
      </div>

      <!-- Main Form -->
      <form enctype="multipart/form-data" action="import.php" method="post" id="importMainForm">
        
        <!-- Filter Tabs & Instant Search Toolbar -->
        <div class="import-toolbar">
          <div class="category-tabs" role="tablist">
            <button type="button" class="cat-tab active" data-filter="all">
              🌟 Όλα
              <span class="tab-badge">15</span>
            </button>
            <button type="button" class="cat-tab" data-filter="schools">
              🏫 Σχολεία & Μαθητές
              <span class="tab-badge">5</span>
            </button>
            <button type="button" class="cat-tab" data-filter="permanent">
              👔 Μόνιμοι
              <span class="tab-badge">6</span>
            </button>
            <button type="button" class="cat-tab" data-filter="substitutes">
              ⏱️ Αναπληρωτές
              <span class="tab-badge">4</span>
            </button>
            <button type="button" class="cat-tab" data-filter="tools">
              🛠️ Εξειδικευμένοι Οδηγοί
              <span class="tab-badge">5</span>
            </button>
          </div>

          <div class="search-box-wrapper">
            <svg class="search-icon-svg" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="importSearch" class="search-input" placeholder="Αναζήτηση (π.χ. τοποθετήσεις, myschool, σχόλια)..." autocomplete="off">
            <button type="button" id="clearSearch" class="clear-search-btn" title="Καθαρισμός αναζήτησης">✕</button>
          </div>
        </div>

        <!-- Options Container -->
        <div id="optionsContainer">

          <!-- Category 1: Σχολεία & Μαθητές -->
          <div class="category-group" data-category="schools">
            <div class="group-header">
              <h3 class="group-title">
                🏫 Σχολεία & Μαθητές
              </h3>
              <p class="group-desc">Μαζική εισαγωγή σχολικών μονάδων, τμημάτων και μαθητικού δυναμικού</p>
            </div>
            
            <div class="options-grid">
              <!-- Card: Schools CSV -->
              <div class="option-card" data-type="2" data-name="Σχολεία (Πρότυπο CSV)" data-cols="12" data-sample="templates/schools.csv" data-delim=";" data-table="school" data-keywords="σχολεια nεα σχολικες μοναδες csv">
                <input type="radio" name="type" value="2" id="type_2">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-blue">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-3"></path><path d="M9 9v.01"></path><path d="M9 12v.01"></path><path d="M9 15v.01"></path><path d="M9 18v.01"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Σχολεία (Πρότυπο CSV)</h4>
                      <p class="card-desc">Εισαγωγή νέων σχολικών μονάδων (κωδικός ΥΠΑΙΘ, ονομασία, τηλέφωνο, email κλπ.).</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">12 στήλες CSV</span>
                    <span class="chip">Οριοθέτης ;</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/schools.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: school</span>
                </div>
              </div>

              <!-- Card: Schools MySchool 2.2 -->
              <div class="option-card" data-type="22" data-name="Σχολεία από MySchool (Αναφορά 2.2)" data-cols="73" data-sample="" data-delim=";" data-table="school" data-keywords="myschool 2.2 εκτεταμενα στοιχεια σχολικων μοναδων αναφορα">
                <input type="radio" name="type" value="22" id="type_22">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-blue">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Σχολεία από MySchool (2.2)</h4>
                      <p class="card-desc">Απευθείας εξαγωγή από την αναφορά «2.2. Εκτεταμένα Στοιχεία Σχολικών Μονάδων».</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">MySchool · 73 στήλες</span>
                    <span class="chip">Χωρίς μετατροπή</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <span style="font-size: 11.5px; color: var(--slate-500);">Απευθείας από MySchool</span>
                  <span class="chip">Πίνακας: school</span>
                </div>
              </div>

              <!-- Card: Students DS -->
              <div class="option-card" data-type="3" data-name="Μαθητές & Τμήματα Δημοτικών (Δ.Σ.)" data-cols="18" data-sample="templates/students_ds.csv" data-delim=";" data-table="school" data-warning="ΠΡΟΣΟΧΗ: Να εισάγονται ΜΟΝΟ αφού αλλάξει το σχολικό έτος! Το προηγούμενο έτος αρχειοθετείται αυτόματα." data-keywords="μαθητες τμηματα δημοτικων δ.σ. ds ολοημερο πρωινη ζωνη ενταξης">
                <input type="radio" name="type" value="3" id="type_3">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-emerald">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαθητές / Τμήματα Δ.Σ.</h4>
                      <p class="card-desc">Ενημέρωση μαθητών ανά τάξη (Α-ΣΤ), Ολοήμερου & Τμημάτων Ένταξης.</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">18 στήλες CSV</span>
                    <span class="chip chip-warning">⚠️ Μετά την αλλαγή σχ. έτους</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/students_ds.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <a href="import_check.php" class="btn-secondary-link" onclick="event.stopPropagation();" title="Έλεγχος προηγούμενης αρχειοθέτησης">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Έλεγχος
                  </a>
                </div>
              </div>

              <!-- Card: Students Nip -->
              <div class="option-card" data-type="4" data-name="Μαθητές & Τμήματα Νηπιαγωγείων (Νηπ.)" data-cols="13" data-sample="templates/students_nip.csv" data-delim=";" data-table="school" data-warning="ΠΡΟΣΟΧΗ: Να εισάγονται ΜΟΝΟ αφού αλλάξει το σχολικό έτος! Το προηγούμενο έτος αρχειοθετείται αυτόματα." data-keywords="μαθητες τμηματα νηπιαγωγειων νηπ κλασικο ολοημερο ενταξη">
                <input type="radio" name="type" value="4" id="type_4">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-emerald">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαθητές / Τμήματα Νηπ.</h4>
                      <p class="card-desc">Ενημέρωση τμημάτων κλασικού, ολοήμερου νηπιαγωγείου και ένταξης.</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">13 στήλες CSV</span>
                    <span class="chip chip-warning">⚠️ Μετά την αλλαγή σχ. έτους</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/students_nip.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: school</span>
                </div>
              </div>

              <!-- Wizard Link Card: Organikes -->
              <div class="option-card" data-wizard="true" data-keywords="οργανικες θεσεις σχολειων ειδικοτητες οδηγος wizard">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-purple">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Εισαγωγή Οργανικών Θέσεων</h4>
                      <p class="card-desc">Εξειδικευμένος οδηγός εισαγωγής οργανικών θέσεων ανά σχολείο και ειδικότητα.</p>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-purple">Οδηγός Wizard</span>
                    <span class="chip">Excel / CSV</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="import_organikes.php" class="btn-wizard">
                    Άνοιγμα Οδηγού
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                  </a>
                  <span style="font-size: 11.5px; color: var(--slate-500);">Ξεχωριστό εργαλείο</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Category 2: Μόνιμοι Εκπαιδευτικοί -->
          <div class="category-group" data-category="permanent">
            <div class="group-header">
              <h3 class="group-title">
                👔 Μόνιμοι Εκπαιδευτικοί
              </h3>
              <p class="group-desc">Μαζική εισαγωγή στοιχείων προσωπικού, τοποθετήσεων υπηρετήσεων και σχολίων</p>
            </div>
            
            <div class="options-grid">
              <!-- Card: Permanent Employees -->
              <div class="option-card" data-type="1" data-name="Μόνιμοι Εκπαιδευτικοί (Πλήρη Στοιχεία)" data-cols="25" data-sample="templates/employees.csv" data-delim=";" data-table="employee & yphrethsh" data-keywords="μονιμοι εκπαιδευτικοι πληρη στοιχεια am afm mk βαθμος διορισμος">
                <input type="radio" name="type" value="1" id="type_1">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-indigo">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μόνιμοι Εκπαιδευτικοί</h4>
                      <p class="card-desc">Εισαγωγή νέων μόνιμων εκπ/κών (ΑΜ, ΑΦΜ, κλάδος, βαθμός, ΜΚ, οργανική & υπηρέτηση).</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">25 στήλες CSV</span>
                    <span class="chip">Πλήρης καρτέλα</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/employees.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: employee</span>
                </div>
              </div>

              <!-- Card: Topothetiseis Monimwn -->
              <div class="option-card" data-type="5" data-name="Μαζικές Τοποθετήσεις Μονίμων" data-cols="3" data-sample="templates/topo.csv" data-delim=";" data-table="yphrethsh" data-keywords="μαζικες τοποθετησεις μονιμων υπηρετησεις ωρες σχολεια">
                <input type="radio" name="type" value="5" id="type_5">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-indigo">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαζικές Τοποθετήσεις Μονίμων</h4>
                      <p class="card-desc">Προσθήκη νέων υπηρετήσεων μονίμων χωρίς διαγραφή των υπαρχουσών τοποθετήσεων.</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">3 στήλες CSV</span>
                    <span class="chip">ΑΜ/ΑΦΜ; Κωδ.Σχολείου; Ώρες</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/topo.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: yphrethsh</span>
                </div>
              </div>

              <!-- Card: Topothetiseis Monimwn Antikatastash -->
              <div class="option-card" data-type="6" data-name="Τοποθετήσεις Μονίμων (με Αντικατάσταση)" data-cols="3" data-sample="templates/topo.csv" data-delim=";" data-table="yphrethsh" data-keywords="τοποθετησεις μονιμων αντικατασταση αποσπασεις διαγραφη προηγουμενων">
                <input type="radio" name="type" value="6" id="type_6">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-indigo">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Τοποθετήσεις (με Αντικατάσταση)</h4>
                      <p class="card-desc">Διαγράφει τις προηγούμενες υπηρετήσεις του έτους και καταχωρεί νέες (για αποσπάσεις).</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">3 στήλες CSV</span>
                    <span class="chip chip-warning">Κατάλληλο για Αποσπάσεις</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/topo.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: yphrethsh</span>
                </div>
              </div>

              <!-- Card: Comments -->
              <div class="option-card" data-type="8" data-name="Μαζική Προσθήκη Σχολίων" data-cols="2" data-sample="templates/comments.csv" data-delim=";" data-table="employee" data-keywords="σχολια παρατηρησεις μονιμων εκπαιδευτικων καρτελα">
                <input type="radio" name="type" value="8" id="type_8">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-amber">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαζική Προσθήκη Σχολίων</h4>
                      <p class="card-desc">Προσθήκη παρατηρήσεων/σχολίων στην καρτέλα των μόνιμων βάσει ΑΜ ή ΑΦΜ.</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">2 στήλες CSV</span>
                    <span class="chip">ΑΜ/ΑΦΜ; Σχόλιο</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/comments.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: employee</span>
                </div>
              </div>

              <!-- Wizard Link Card: Postgrad -->
              <div class="option-card" data-wizard="true" data-keywords="μεταπτυχιακοι τιτλοι διδακτορικα εκπαιδευτικων postgrad οδηγος">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-purple">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Εισαγωγή Μεταπτυχιακών Τίτλων</h4>
                      <p class="card-desc">Οδηγός καταχώρισης μεταπτυχιακών/διδακτορικών τίτλων εκπαιδευτικών από Excel.</p>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-purple">Οδηγός Wizard</span>
                    <span class="chip">Excel (.xls)</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="import_postgrad.php" class="btn-wizard">
                    Άνοιγμα Οδηγού
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                  </a>
                  <span style="font-size: 11.5px; color: var(--slate-500);">Ξεχωριστό εργαλείο</span>
                </div>
              </div>

              <!-- Wizard Link Card: Yphrethsh XLS -->
              <div class="option-card" data-wizard="true" data-keywords="υπηρετησεις τοποθετησεις xls excel συγκεντρωτικο αρχειο yphrethsh">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-purple">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Εισαγωγή Υπηρετήσεων (από XLS)</h4>
                      <p class="card-desc">Εισαγωγή υπηρετήσεων και ωραρίων από το κλασικό συγκεντρωτικό φύλλο Excel τοποθετήσεων.</p>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-purple">Οδηγός Wizard</span>
                    <span class="chip">Excel (.xls)</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="import_yphrethsh.php" class="btn-wizard">
                    Άνοιγμα Οδηγού
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                  </a>
                  <span style="font-size: 11.5px; color: var(--slate-500);">Ξεχωριστό εργαλείο</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Category 3: Αναπληρωτές Εκπαιδευτικοί -->
          <div class="category-group" data-category="substitutes">
            <div class="group-header">
              <h3 class="group-title">
                ⏱️ Αναπληρωτές Εκπαιδευτικοί
              </h3>
              <p class="group-desc">Μαζικές τοποθετήσεις αναπληρωτών και ανάθεση σε προγράμματα / πράξεις (ΕΣΠΑ)</p>
            </div>
            
            <div class="options-grid">
              <!-- Card: Topothetiseis Anapl -->
              <div class="option-card" data-type="7" data-name="Μαζικές Τοποθετήσεις Αναπληρωτών" data-cols="4" data-sample="templates/topo_anapl.csv" data-delim=";" data-table="ektaktoi & yphrethsh_ekt" data-keywords="αναπληρωτες τοποθετησεις ωραριο ωρες σχολεια ektaktoi">
                <input type="radio" name="type" value="7" id="type_7">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-emerald">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαζικές Τοποθετήσεις Αναπληρωτών</h4>
                      <p class="card-desc">Τοποθέτηση αναπληρωτών σε σχολεία (ΑΦΜ, ωράριο, κωδικός σχολείου, ώρες).</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">4 στήλες CSV</span>
                    <span class="chip">Μόνο με ΑΦΜ</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/topo_anapl.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: ektaktoi</span>
                </div>
              </div>

              <!-- Card: Assign Praxi -->
              <div class="option-card" data-type="9" data-name="Μαζική Ανάθεση σε Πράξεις (ΕΣΠΑ)" data-cols="2" data-sample="templates/praxi.csv" data-delim=";" data-table="ektaktoi" data-keywords="αναθεση πραξεις εσπα αναπληρωτες praxi χρηματοδοτηση">
                <input type="radio" name="type" value="9" id="type_9">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-emerald">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M9 14l2 2 4-4"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Μαζική Ανάθεση σε Πράξεις</h4>
                      <p class="card-desc">Σύνδεση αναπληρωτών με συγκεκριμένη πράξη / χρηματοδότηση (ΑΦΜ, ID πράξης).</p>
                    </div>
                    <div class="card-check-bubble">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-accent">2 στήλες CSV</span>
                    <span class="chip">ΑΦΜ; ID Πράξης</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="templates/praxi.csv" class="btn-sample" download onclick="event.stopPropagation();">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Δείγμα CSV
                  </a>
                  <span class="chip">Πίνακας: ektaktoi</span>
                </div>
              </div>

              <!-- Wizard Link Card: Ektaktoi Excel -->
              <div class="option-card" data-wizard="true" data-keywords="εισαγωγη αναπληρωτων excel ektaktoi μαζικη οδηγος">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-purple">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M8 13h8"></path><path d="M8 17h8"></path><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Εισαγωγή Αναπληρωτών (Excel)</h4>
                      <p class="card-desc">Εξειδικευμένος οδηγός εισαγωγής νέων αναπληρωτών εκπαιδευτικών από αρχείο Excel.</p>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-purple">Οδηγός Wizard</span>
                    <span class="chip">Excel (.xls)</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="ektaktoi_import.php" class="btn-wizard">
                    Άνοιγμα Οδηγού
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                  </a>
                  <span style="font-size: 11.5px; color: var(--slate-500);">Ξεχωριστό εργαλείο</span>
                </div>
              </div>

              <!-- Wizard Link Card: Ektaktoi Minedu -->
              <div class="option-card" data-wizard="true" data-keywords="εισαγωγη αναπληρωτων εκπαιδευτικων απο excel ektaktoi_import_minedu.php υπουργειο minedu προσληψεις αναληψεις">
                <div>
                  <div class="card-header-row">
                    <div class="card-icon icon-purple">
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    </div>
                    <div class="card-heading-box">
                      <h4 class="card-title">Εισαγωγή Αναπληρωτών Εκπαιδευτικών από Excel</h4>
                      <p class="card-desc">Απευθείας ανάγνωση και εισαγωγή από τα επίσημα αρχεία Excel του Υπουργείου (αρχεία προσλήψεων & αναλήψεων).</p>
                    </div>
                  </div>
                  <div class="card-badges">
                    <span class="chip chip-purple">Οδηγός Wizard</span>
                    <span class="chip">Excel (.xlsx, .xls)</span>
                    <span class="chip chip-accent">Αρχεία ΥΠΑΙΘ</span>
                  </div>
                </div>
                <div class="card-footer-actions">
                  <a href="ektaktoi_import_minedu.php" class="btn-wizard">
                    Άνοιγμα Οδηγού
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                  </a>
                  <span style="font-size: 11.5px; color: var(--slate-500);">ektaktoi_import_minedu.php</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty search results placeholder -->
          <div id="emptySearchState" class="empty-search-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 10px auto; color: var(--slate-400);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <h4>Δεν βρέθηκε αντίστοιχη ενέργεια</h4>
            <p>Δοκιμάστε διαφορετικό όρο αναζήτησης ή επιλέξτε την καρτέλα «Όλα».</p>
          </div>

        </div>

        <!-- Step 2: Dynamic Assistant Box -->
        <div id="dynamicAssistantBox" class="assistant-box empty">
          <div class="assistant-header">
            <h4 class="assistant-title" id="assistantTitle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
              <span>Βήμα 2: Επιλέξτε έναν τύπο εισαγωγής παραπάνω για να δείτε οδηγίες</span>
            </h4>
            <div id="assistantDownloadContainer" style="display: none;">
              <a href="#" id="assistantDownloadLink" class="btn-sample" download>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Κατέβασμα Δείγματος CSV
              </a>
            </div>
          </div>
          
          <div id="assistantDetailsGrid" class="assistant-details-grid" style="display: none;">
            <div class="detail-card">
              <div class="detail-label">Ενεργη Επιλογη</div>
              <div class="detail-val" id="assistantSelectedType">-</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Απαιτουμενες Στηλες</div>
              <div class="detail-val" id="assistantColumnsCount">-</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Διαχωριστικο Στηλων</div>
              <div class="detail-val" id="assistantDelimiter">Ελληνικό ερωτηματικό (;)</div>
            </div>
            <div class="detail-card">
              <div class="detail-label">Πινακας Προορισμου</div>
              <div class="detail-val" id="assistantTargetTable">-</div>
            </div>
          </div>

          <div id="assistantWarningBox" class="assistant-warning" style="display: none;"></div>
        </div>

        <!-- Step 3: File Upload Dropzone -->
        <div class="upload-section-card" id="step3Section">
          <div class="upload-section-header">
            <h3>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
              Βήμα 3: Επιλογή & Μεταφόρτωση Αρχείου
            </h3>
            <p>Σύρετε το συμπληρωμένο αρχείο CSV στο παρακάτω πλαίσιο ή πατήστε για αναζήτηση στον υπολογιστή σας.</p>
          </div>

          <div class="dropzone-container" id="fileDropzone">
            <input type="file" name="filename" id="csvFileInput" accept=".csv" required style="display: none;">
            <div class="dropzone-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            </div>
            <div class="dropzone-main-text">Σύρετε και αφήστε το αρχείο CSV εδώ</div>
            <div class="dropzone-sub-text">ή κάντε κλικ για να επιλέξετε από τα αρχεία σας</div>
            <span class="btn-browse-file">Επιλογή αρχείου...</span>
          </div>

          <!-- Selected File Preview Card -->
          <div class="file-preview-card" id="filePreviewCard">
            <div class="file-preview-details">
              <div class="file-icon-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
              </div>
              <div>
                <div class="file-name-text" id="previewFileName">filename.csv</div>
                <div class="file-size-text" id="previewFileSize">0 KB · Έτοιμο για μεταφόρτωση</div>
              </div>
            </div>
            <button type="button" class="btn-remove-file" id="btnRemoveFile">✕ Αφαίρεση</button>
          </div>

          <!-- Column Validation Feedback Card -->
          <div class="validation-feedback-card" id="validationFeedbackCard" style="display: none;">
            <div class="validation-feedback-header">
              <div class="validation-status-icon" id="validationStatusIcon"></div>
              <div class="validation-status-body">
                <h4 class="validation-status-title" id="validationStatusTitle">Έλεγχος Στηλών</h4>
                <div class="validation-status-desc" id="validationStatusDesc"></div>
                <div class="validation-detected-headers" id="validationDetectedHeaders" style="display: none;">
                  <span class="detected-label">Επικεφαλίδες που εντοπίστηκαν στο αρχείο:</span>
                  <div class="detected-tags-list" id="validationDetectedTags"></div>
                </div>
              </div>
            </div>
            <div class="validation-actions-row" id="validationActionsRow" style="display: none;"></div>
          </div>

          <!-- Tips & Best Practices Collapsible Drawer -->
          <details class="tips-accordion">
            <summary>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
              Χρήσιμες συμβουλές & προδιαγραφές αρχείων CSV
            </summary>
            <ul class="tips-list">
              <li><strong>Οριοθέτης στηλών:</strong> Χρησιμοποιείτε πάντα το ελληνικό ερωτηματικό (<code>;</code>) ως διαχωριστικό πεδίων.</li>
              <li><strong>Κωδικοποίηση:</strong> Προτείνεται η αποθήκευση του αρχείου με κωδικοποίηση <strong>UTF-8</strong> για σωστή απόδοση των ελληνικών ονομάτων.</li>
              <li><strong>Επικεφαλίδες:</strong> Η πρώτη γραμμή του αρχείου θεωρείται ότι περιέχει τίτλους στηλών και <strong>παραλείπεται αυτόματα</strong> κατά την εισαγωγή.</li>
              <li><strong>Χρόνος επεξεργασίας:</strong> Για μεγάλα αρχεία (πάνω από 500 εγγραφές), η διαδικασία μπορεί να διαρκέσει 1-2 λεπτά. Παρακαλούμε μην ανανεώνετε τη σελίδα.</li>
            </ul>
          </details>
        </div>

        <!-- Submit & Navigation Bar -->
        <div class="submit-actions-bar">
          <input type="button" class="btn-return-home" value="↩️ Επιστροφή στην Αρχική" onClick="parent.location='../index.php'">
          
          <button type="submit" name="submit" id="btnSubmitImport" class="btn-import-submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span id="btnSubmitText">Μεταφόρτωση & Εκτέλεση Εισαγωγής</span>
          </button>
        </div>

      </form>
    </div>

    <!-- Client-side Interactive Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const optionCards = document.querySelectorAll('.option-card');
      const catTabs = document.querySelectorAll('.cat-tab');
      const categoryGroups = document.querySelectorAll('.category-group');
      const searchInput = document.getElementById('importSearch');
      const clearSearchBtn = document.getElementById('clearSearch');
      const emptySearchState = document.getElementById('emptySearchState');
      
      const assistantBox = document.getElementById('dynamicAssistantBox');
      const assistantTitle = document.getElementById('assistantTitle');
      const assistantDownloadContainer = document.getElementById('assistantDownloadContainer');
      const assistantDownloadLink = document.getElementById('assistantDownloadLink');
      const assistantDetailsGrid = document.getElementById('assistantDetailsGrid');
      const assistantSelectedType = document.getElementById('assistantSelectedType');
      const assistantColumnsCount = document.getElementById('assistantColumnsCount');
      const assistantDelimiter = document.getElementById('assistantDelimiter');
      const assistantTargetTable = document.getElementById('assistantTargetTable');
      const assistantWarningBox = document.getElementById('assistantWarningBox');

      const fileDropzone = document.getElementById('fileDropzone');
      const fileInput = document.getElementById('csvFileInput');
      const filePreviewCard = document.getElementById('filePreviewCard');
      const previewFileName = document.getElementById('previewFileName');
      const previewFileSize = document.getElementById('previewFileSize');
      const btnRemoveFile = document.getElementById('btnRemoveFile');

      const validationFeedbackCard = document.getElementById('validationFeedbackCard');
      const validationStatusIcon = document.getElementById('validationStatusIcon');
      const validationStatusTitle = document.getElementById('validationStatusTitle');
      const validationStatusDesc = document.getElementById('validationStatusDesc');
      const validationDetectedHeaders = document.getElementById('validationDetectedHeaders');
      const validationDetectedTags = document.getElementById('validationDetectedTags');
      const validationActionsRow = document.getElementById('validationActionsRow');

      const stepIndicator1 = document.getElementById('stepIndicator1');
      const stepIndicator2 = document.getElementById('stepIndicator2');
      const stepIndicator3 = document.getElementById('stepIndicator3');

      const importMainForm = document.getElementById('importMainForm');
      const btnSubmitImport = document.getElementById('btnSubmitImport');
      const btnSubmitText = document.getElementById('btnSubmitText');

      let currentActiveFilter = 'all';
      let currentParsedCsv = null;
      let currentValidationStatus = null; // 'success', 'error', 'info', or null

      // 1. Option Card Click Handling
      optionCards.forEach(card => {
        card.addEventListener('click', function(e) {
          // If wizard card, follow its link
          if (card.dataset.wizard === 'true') {
            const link = card.querySelector('a.btn-wizard');
            if (link) link.click();
            return;
          }

          const radio = card.querySelector('input[type="radio"]');
          if (!radio) return;

          // Unselect other cards
          optionCards.forEach(c => c.classList.remove('selected'));
          card.classList.add('selected');
          radio.checked = true;

          // Update Step Indicators
          stepIndicator1.classList.add('completed');
          stepIndicator2.classList.add('active');

          // Update Assistant Box
          updateAssistantBox(card);

          // Update Submit Button Text
          const typeName = card.dataset.name || 'Δεδομένων';
          btnSubmitText.textContent = 'Εισαγωγή: ' + typeName;

          // If a file is already loaded, re-validate columns against this selection
          if (currentParsedCsv) {
            validateColumnsWithSelection();
          }
        });
      });

      function updateAssistantBox(card) {
        const name = card.dataset.name || '';
        const cols = card.dataset.cols || '-';
        const sample = card.dataset.sample || '';
        const delim = card.dataset.delim || ';';
        const table = card.dataset.table || '-';
        const warning = card.dataset.warning || '';

        assistantBox.classList.remove('empty');
        assistantTitle.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg><span>Ενεργή Επιλογή: <strong>' + escapeHtml(name) + '</strong></span>';
        
        assistantSelectedType.textContent = name;
        assistantColumnsCount.textContent = cols + ' στήλες';
        assistantDelimiter.textContent = 'Ελληνικό ερωτηματικό (' + delim + ')';
        assistantTargetTable.textContent = table;
        assistantDetailsGrid.style.display = 'grid';

        if (sample) {
          assistantDownloadLink.href = sample;
          assistantDownloadContainer.style.display = 'block';
        } else {
          assistantDownloadContainer.style.display = 'none';
        }

        if (warning) {
          assistantWarningBox.textContent = warning;
          assistantWarningBox.style.display = 'block';
        } else {
          assistantWarningBox.style.display = 'none';
        }
      }

      function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
      }

      // 2. Tab Filtering
      catTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          catTabs.forEach(t => t.classList.remove('active'));
          this.classList.add('active');
          currentActiveFilter = this.dataset.filter;
          applyFilters();
        });
      });

      // 3. Search Filtering
      searchInput.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
          clearSearchBtn.style.display = 'block';
        } else {
          clearSearchBtn.style.display = 'none';
        }
        applyFilters();
      });

      clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        applyFilters();
        searchInput.focus();
      });

      function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        let totalVisibleCards = 0;

        categoryGroups.forEach(group => {
          const groupCat = group.dataset.category;
          const cards = group.querySelectorAll('.option-card');
          let visibleInGroup = 0;

          cards.forEach(card => {
            const isWizard = card.dataset.wizard === 'true';
            
            // Check Category filter
            let matchesCategory = false;
            if (currentActiveFilter === 'all') {
              matchesCategory = true;
            } else if (currentActiveFilter === 'tools') {
              matchesCategory = isWizard;
            } else {
              matchesCategory = (groupCat === currentActiveFilter);
            }

            // Check Search query
            let matchesQuery = true;
            if (query.length > 0) {
              const textContent = (card.textContent || '').toLowerCase();
              const keywords = (card.dataset.keywords || '').toLowerCase();
              matchesQuery = textContent.includes(query) || keywords.includes(query);
            }

            if (matchesCategory && matchesQuery) {
              card.style.display = 'flex';
              visibleInGroup++;
              totalVisibleCards++;
            } else {
              card.style.display = 'none';
            }
          });

          // Show or hide the whole category section
          if (visibleInGroup > 0) {
            group.style.display = 'block';
          } else {
            group.style.display = 'none';
          }
        });

        // Toggle Empty Search State
        if (totalVisibleCards === 0) {
          emptySearchState.style.display = 'block';
        } else {
          emptySearchState.style.display = 'none';
        }
      }

      // 4. File Dropzone & Selection Handling
      fileDropzone.addEventListener('click', function() {
        fileInput.click();
      });

      fileInput.addEventListener('change', function() {
        handleFileSelected(this.files);
      });

      ['dragenter', 'dragover'].forEach(eventName => {
        fileDropzone.addEventListener(eventName, function(e) {
          e.preventDefault();
          e.stopPropagation();
          fileDropzone.classList.add('dragover');
        }, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        fileDropzone.addEventListener(eventName, function(e) {
          e.preventDefault();
          e.stopPropagation();
          fileDropzone.classList.remove('dragover');
        }, false);
      });

      fileDropzone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
          fileInput.files = files;
          handleFileSelected(files);
        }
      }, false);

      function handleFileSelected(files) {
        if (!files || files.length === 0) return;
        const file = files[0];
        
        // Validate CSV extension
        if (!file.name.toLowerCase().endsWith('.csv')) {
          alert('Παρακαλώ επιλέξτε αρχείο με κατάληξη .csv');
          fileInput.value = '';
          currentParsedCsv = null;
          filePreviewCard.style.display = 'none';
          hideValidationFeedback();
          fileDropzone.style.display = 'block';
          stepIndicator3.classList.remove('completed', 'active');
          return;
        }

        const sizeFormatted = formatFileSize(file.size);

        previewFileName.textContent = file.name;
        previewFileSize.textContent = sizeFormatted + ' · Γίνεται έλεγχος στηλών...';

        fileDropzone.style.display = 'none';
        filePreviewCard.style.display = 'flex';
        stepIndicator3.classList.add('completed');

        // Parse CSV file and perform column count validation
        parseFileAndValidate(file);
      }

      // Helper: Format file size
      function formatFileSize(bytes) {
        if (bytes < 1024) {
          return bytes + ' bytes';
        } else if (bytes < 1024 * 1024) {
          return (bytes / 1024).toFixed(1) + ' KB';
        } else {
          return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
      }

      // Helper: Parse a single CSV line respecting quotes
      function parseCsvLine(line, delimiter) {
        const fields = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
          const char = line[i];
          if (char === '"') {
            if (inQuotes && line[i + 1] === '"') {
              current += '"';
              i++;
            } else {
              inQuotes = !inQuotes;
            }
          } else if (char === delimiter && !inQuotes) {
            fields.push(current.trim());
            current = '';
          } else {
            current += char;
          }
        }
        fields.push(current.trim());
        return fields;
      }

      // Read initial slice of CSV file, parse columns and trigger validation
      function parseFileAndValidate(file) {
        const sliceSize = Math.min(file.size, 65536);
        const blob = file.slice(0, sliceSize);
        const reader = new FileReader();

        reader.onload = function(e) {
          let text = e.target.result || '';
          if (text.charCodeAt(0) === 0xFEFF) {
            text = text.substring(1);
          }

          const lines = text.split(/\r\n|\n|\r/);
          const validLines = [];
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].trim().length > 0) {
              validLines.push(lines[i]);
              if (validLines.length >= 3) break;
            }
          }

          if (validLines.length === 0) {
            currentParsedCsv = {
              isEmpty: true,
              name: file.name,
              size: file.size,
              sizeFormatted: formatFileSize(file.size)
            };
            validateColumnsWithSelection();
            return;
          }

          const headerLine = validLines[0];
          const semiFields = parseCsvLine(headerLine, ';');
          const commaFields = parseCsvLine(headerLine, ',');
          
          let row2SemiCount = null;
          let row2CommaCount = null;
          if (validLines.length > 1) {
            row2SemiCount = parseCsvLine(validLines[1], ';').length;
            row2CommaCount = parseCsvLine(validLines[1], ',').length;
          }

          const hasTrailingEmptyCol = (semiFields.length > 1 && semiFields[semiFields.length - 1] === '');

          currentParsedCsv = {
            isEmpty: false,
            name: file.name,
            size: file.size,
            sizeFormatted: formatFileSize(file.size),
            semicolonCols: semiFields.length,
            commaCols: commaFields.length,
            headers: semiFields,
            row2SemiCount: row2SemiCount,
            row2CommaCount: row2CommaCount,
            hasTrailingEmptyCol: hasTrailingEmptyCol
          };

          validateColumnsWithSelection();
        };

        reader.onerror = function() {
          currentParsedCsv = null;
          hideValidationFeedback();
        };

        reader.readAsText(blob);
      }

      // Check detected column count against currently selected option card
      function validateColumnsWithSelection() {
        if (!currentParsedCsv) {
          hideValidationFeedback();
          return;
        }

        if (currentParsedCsv.isEmpty) {
          currentValidationStatus = 'error';
          showValidationFeedback({
            status: 'error',
            title: 'Το αρχείο είναι κενό',
            desc: 'Το αρχείο που επιλέξατε δεν περιέχει γραμμές δεδομένων ή επικεφαλίδες. Παρακαλώ επιλέξτε ένα έγκυρο αρχείο CSV.',
            previewSubtitle: currentParsedCsv.sizeFormatted + ' · Κενό αρχείο · Σφάλμα'
          });
          return;
        }

        const selectedCard = document.querySelector('.option-card.selected');

        // Case 1: No card selected yet in Step 1
        if (!selectedCard) {
          currentValidationStatus = 'info';
          showValidationFeedback({
            status: 'info',
            title: 'Το αρχείο φορτώθηκε (' + currentParsedCsv.semicolonCols + ' στήλες)',
            desc: 'Εντοπίστηκαν <strong>' + currentParsedCsv.semicolonCols + ' στήλες</strong> στο αρχείο «' + escapeHtml(currentParsedCsv.name) + '». Παρακαλούμε επιλέξτε την κατάλληλη ενέργεια στο <strong>Βήμα 1</strong> για να ελεγχθεί αν οι στήλες συμφωνούν με τη βάση δεδομένων.',
            previewSubtitle: currentParsedCsv.sizeFormatted + ' · ' + currentParsedCsv.semicolonCols + ' στήλες εντοπίστηκαν',
            headers: currentParsedCsv.headers
          });
          return;
        }

        // Case 2: Card is selected
        const expectedCols = parseInt(selectedCard.dataset.cols, 10);
        const typeName = selectedCard.dataset.name || 'Δεδομένων';
        const detectedCols = currentParsedCsv.semicolonCols;
        const commaCols = currentParsedCsv.commaCols;
        const sampleUrl = selectedCard.dataset.sample || '';

        if (detectedCols === expectedCols) {
          // Success: Column counts match exactly
          currentValidationStatus = 'success';
          showValidationFeedback({
            status: 'success',
            title: '✓ Έλεγχος στηλών επιτυχής (' + detectedCols + ' στήλες)',
            desc: 'Το αρχείο «<strong>' + escapeHtml(currentParsedCsv.name) + '</strong>» περιέχει <span class="validation-col-badge detected-ok">' + detectedCols + ' στήλες</span>, όσες ακριβώς απαιτούνται για την επιλογή «<strong>' + escapeHtml(typeName) + '</strong>». Το αρχείο είναι έτοιμο για εισαγωγή!',
            previewSubtitle: currentParsedCsv.sizeFormatted + ' · ' + detectedCols + ' στήλες · Έλεγχος επιτυχής ✓',
            headers: currentParsedCsv.headers
          });
        } else {
          // Error: Column mismatch
          currentValidationStatus = 'error';

          let errorHtml = 'Το επιλεγμένο αρχείο «<strong>' + escapeHtml(currentParsedCsv.name) + '</strong>» περιέχει <span class="validation-col-badge detected-err">' + detectedCols + ' στήλες</span>, ενώ για την ενέργεια «<strong>' + escapeHtml(typeName) + '</strong>» απαιτούνται αυστηρά <span class="validation-col-badge expected">' + expectedCols + ' στήλες</span>.';

          // Delimiter diagnosis (comma vs semicolon)
          if (commaCols === expectedCols || (detectedCols === 1 && commaCols > 1)) {
            errorHtml += '<div class="validation-subnote">💡 <strong>Πιθανή αιτία:</strong> Το αρχείο φαίνεται να είναι διαχωρισμένο με <strong>κόμμα (,)</strong> (εντοπίστηκαν ' + commaCols + ' στήλες με κόμμα) αντί για <strong>ελληνικό ερωτηματικό (;)</strong>.<br>Ανοίξτε το αρχείο στο Excel και επιλέξτε <em>Αποθήκευση ως &rarr; CSV (οριοθετημένο με ερωτηματικό)</em>.</div>';
          } else if (currentParsedCsv.hasTrailingEmptyCol && detectedCols - 1 === expectedCols) {
            errorHtml += '<div class="validation-subnote">💡 <strong>Σημείωση:</strong> Εντοπίστηκε κενή στήλη στο τέλος της γραμμής (πιθανόν περιττό ερωτηματικό <code>;</code> στο τέλος κάθε γραμμής). Αφαιρέστε το τελευταίο ερωτηματικό ώστε το αρχείο να έχει ακριβώς ' + expectedCols + ' στήλες.</div>';
          } else {
            errorHtml += '<div class="validation-subnote">⚠️ <strong>Προσοχή:</strong> Η εκτέλεση της εισαγωγής με λάθος αριθμό στηλών θα αποτύχει στη βάση δεδομένων. Βεβαιωθείτε ότι επιλέξατε το σωστό αρχείο ή κατεβάστε το αντίστοιχο πρότυπο παρακάτω.</div>';
          }

          let actionsHtml = '';
          if (sampleUrl) {
            actionsHtml += '<a href="' + escapeHtml(sampleUrl) + '" class="btn-val-action sample" download><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>Λήψη σωστού προτύπου CSV (' + expectedCols + ' στήλες)</a>';
          }
          actionsHtml += '<button type="button" class="btn-val-action reselect" id="btnReselectFile"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>Επιλογή άλλου αρχείου</button>';

          showValidationFeedback({
            status: 'error',
            title: '❌ Λανθασμένος αριθμός στηλών (' + detectedCols + ' αντί για ' + expectedCols + ')',
            descHtml: errorHtml,
            actionsHtml: actionsHtml,
            previewSubtitle: currentParsedCsv.sizeFormatted + ' · ' + detectedCols + ' στήλες (Απαιτούνται ' + expectedCols + ') · Λάθος αρχείο'
          });
        }
      }

      function showValidationFeedback(opts) {
        validationFeedbackCard.classList.remove('status-success', 'status-error', 'status-info');
        validationFeedbackCard.classList.add('status-' + opts.status);

        filePreviewCard.classList.remove('status-success', 'status-error', 'status-info');
        filePreviewCard.classList.add('status-' + opts.status);

        if (opts.previewSubtitle) {
          previewFileSize.textContent = opts.previewSubtitle;
        }

        // Set Icon
        if (opts.status === 'success') {
          validationStatusIcon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        } else if (opts.status === 'error') {
          validationStatusIcon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        } else {
          validationStatusIcon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }

        validationStatusTitle.textContent = opts.title;

        if (opts.descHtml) {
          validationStatusDesc.innerHTML = opts.descHtml;
        } else {
          validationStatusDesc.innerHTML = opts.desc || '';
        }

        // Headers Preview Tags
        if (opts.headers && opts.headers.length > 0) {
          validationDetectedHeaders.style.display = 'block';
          validationDetectedTags.innerHTML = '';
          const maxTags = Math.min(opts.headers.length, 8);
          for (let i = 0; i < maxTags; i++) {
            const tagText = opts.headers[i].trim() || '(κενό)';
            const pill = document.createElement('span');
            pill.className = 'header-tag-pill';
            pill.innerHTML = '<span class="pill-num">' + (i + 1) + '.</span> ' + escapeHtml(tagText);
            validationDetectedTags.appendChild(pill);
          }
          if (opts.headers.length > 8) {
            const morePill = document.createElement('span');
            morePill.className = 'header-tag-pill';
            morePill.style.fontWeight = '700';
            morePill.textContent = '+' + (opts.headers.length - 8) + ' ακόμα...';
            validationDetectedTags.appendChild(morePill);
          }
        } else {
          validationDetectedHeaders.style.display = 'none';
        }

        // Actions Row
        if (opts.actionsHtml) {
          validationActionsRow.innerHTML = opts.actionsHtml;
          validationActionsRow.style.display = 'flex';
          const reselectBtn = document.getElementById('btnReselectFile');
          if (reselectBtn) {
            reselectBtn.addEventListener('click', function() {
              fileInput.click();
            });
          }
        } else {
          validationActionsRow.style.display = 'none';
        }

        validationFeedbackCard.style.display = 'flex';
      }

      function hideValidationFeedback() {
        validationFeedbackCard.style.display = 'none';
        validationFeedbackCard.classList.remove('status-success', 'status-error', 'status-info');
        filePreviewCard.classList.remove('status-success', 'status-error', 'status-info');
        currentValidationStatus = null;
      }

      btnRemoveFile.addEventListener('click', function() {
        fileInput.value = '';
        currentParsedCsv = null;
        filePreviewCard.style.display = 'none';
        hideValidationFeedback();
        fileDropzone.style.display = 'block';
        stepIndicator3.classList.remove('completed');
      });

      // 5. Form Submit Validation
      importMainForm.addEventListener('submit', function(e) {
        const checkedType = document.querySelector('input[name="type"]:checked');
        if (!checkedType) {
          e.preventDefault();
          alert('Παρακαλώ επιλέξτε τον τύπο δεδομένων που θέλετε να εισάγετε (Βήμα 1).');
          window.scrollTo({ top: document.getElementById('stepIndicator1').offsetTop - 20, behavior: 'smooth' });
          return false;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
          e.preventDefault();
          alert('Παρακαλώ επιλέξτε ένα αρχείο CSV για μεταφόρτωση (Βήμα 3).');
          window.scrollTo({ top: document.getElementById('step3Section').offsetTop - 20, behavior: 'smooth' });
          return false;
        }

        // Prevent submission if column count validation failed
        if (currentParsedCsv && currentValidationStatus === 'error') {
          e.preventDefault();
          const selectedCard = document.querySelector('.option-card.selected');
          const typeName = selectedCard ? selectedCard.dataset.name : '';
          const expCols = selectedCard ? selectedCard.dataset.cols : '';
          alert('Δεν μπορεί να εκτελεστεί η εισαγωγή: Το αρχείο που επιλέξατε έχει ' + currentParsedCsv.semicolonCols + ' στήλες ενώ για την επιλογή «' + typeName + '» απαιτούνται αυστηρά ' + expCols + ' στήλες.\n\nΠαρακαλούμε ελέγξτε και διορθώστε το αρχείο σας πριν προχωρήσετε.');
          validationFeedbackCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return false;
        }

        // Processing state
        btnSubmitImport.disabled = true;
        btnSubmitText.textContent = '⏳ Γίνεται επεξεργασία & εισαγωγή... Παρακαλώ περιμένετε.';
      });

    });
    </script>
<?php
    echo "</div>"; // close import-page-wrapper
    exit;
  }
		
		
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  if (!isset($_POST['type'])){
    require '../etc/menu.php';
    echo "<div class='import-container'>";
    echo "<div class='result-message result-error'>";
    echo "<h3>Σφάλμα: Δεν επιλέξατε τύπο δεδομένων.</h3>";
    echo "<br><a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
    echo "</div>";
    echo "</div>";
    die();
  }
  //Upload File
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
      require '../etc/menu.php';
      echo "<div class='import-container'>";
      echo "<div class='result-message result-success'>";
      echo "<p><strong>Το αρχείο ". htmlspecialchars($_FILES['filename']['name']) ." ανέβηκε με επιτυχία.</strong></p>";
      echo "</div>";

      //Import uploaded file to Database
      $handle = fopen($_FILES['filename']['tmp_name'], "r");
      switch ($_POST['type'])
      {
          case 1:
          case 5:
          case 6:
          case 8:
            $tbl = 'employee';
            break;
          case 2:
          case 22:
          case 3:
          case 4:
            $tbl = 'school';
            break;
          case 7:
          case 9:
            $tbl = 'ektaktoi';
            break;
      }
      $num = 0;
      $saves = 0;
      $checked = 0;
      $headers = 1;
      $error = false;
      $warnings = 0;
      $warn_msg = '';
      $er_msg = '';
      $top_afm = array();
      $top_wres = array();
      
      // set max execution time (for large files)
      set_time_limit (480);

      // initialize update_queries table
      $update_queries = array();
      $update_yphrethseis = array();
      // read csv line by line
      while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
        // skip header line
        if ($headers){
            $headers = 0;
            continue;
        }
        // check if csv & table columns are equal
        if (!$checked)
        {
          $csvcols = count($data);
          if ($_POST['type'] == 1){
            $tblcols = 25;
          }
          else if ($_POST['type'] == 2){
            $tblcols = 12;
          }
          else if ($_POST['type'] == 3){
            $tblcols = 18;
          }
          else if ($_POST['type'] == 4){
            $tblcols = 13;
          }
          else if ($_POST['type'] == 5 || $_POST['type'] == 6){
            $tblcols = 3;
          }
          else if ($_POST['type'] == 7){
            $tblcols = 4;
          }
          else if ($_POST['type'] == 8 || $_POST['type'] == 9){
            $tblcols = 2;
          }
          else if ($_POST['type'] == 22){
            $tblcols = 73;
          }

          if ($csvcols <> $tblcols)
          {
            echo "<div class='result-message result-error'>";
            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
            echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
            echo "</div>";
            echo "</div>";
            die();
          }
          else
            $checked = 1;
        }  

        switch ($_POST['type']){
          // employees
          case 1:
            // check school codes
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            $sx_organ = getSchoolFromCode($data[23],$mysqlconn);
            $sx_yphr = getSchoolFromCode($data[24],$mysqlconn);
            if (!$sx_organ || !$sx_yphr){
              $error = true;
              $er_msg = 'Σφάλμα: Δε βρέθηκε ο 7ψήφιος κωδικός σχολείου: ';
              $er_msg .= !$sx_organ ? $data[23] : $data[24];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check if am exists
            $qry = "SELECT * FROM employee WHERE am = $data[5]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με ΑΜ ".$data[5]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // fix dates
            $data[7] = parse_date_to_mysql($data[7]);
            $data[10] = parse_date_to_mysql($data[10]);
            $data[11] = parse_date_to_mysql($data[11]);
            $status = 1;
            // proceed to import
            $import="INSERT into employee(name,surname,patrwnymo,mhtrwnymo,klados,am,thesi,fek_dior,hm_dior,
            vathm, mk, hm_mk, hm_anal, met_did, proyp, proyp_not, status,
            afm, tel, address, idnum, amka, email, wres, sx_organikhs, sx_yphrethshs)
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]',0,'$data[6]','$data[7]',
            '$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$status',
            '$data[16]','$data[17]','$data[18]','$data[19]','$data[20]','$data[21]','$data[22]', $sx_organ, $sx_yphr)";
            $imp_8 = safe_iconv_to_utf8($import);
            $update_queries[] = $imp_8;
            
            $saves++;
            // insert yphrethsh as well
            //$id = mysqli_insert_id($mysqlconnection);
            // use am instead of inserted id
            $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
            values ('$data[5]', '$sx_yphr', '$data[22]', '$sx_organ', '$sxol_etos')";
            $update_yphrethseis[$data[5]] = $query;
            
            break;

          // schools - myschool
          case 22:
            // check if school already exists
            $code = trim($data[12],'=\"');
            $qry = "SELECT * FROM school WHERE code = $code";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ) {
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$code." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // columns:
            // Κατηγορία Μοριοδότησης (4),	Δήμος	(6), Είδος	(10) Κωδ. ΥΠΠΘ (12)	Ονομασία (13)	Λειτουργικότητα	(14) 
            // Οργανικότητα	(15) Τηλέφωνο	(17) ΦΑΞ	(18) e-mail	(19) Ταχ. Διεύθυνση	(21) ΤΚ	(22) Αναστολή	(46) 
            $eidos = safe_iconv_to_utf8($data[10]);
            $typos = safe_iconv_to_utf8($data[11]);
            if ($eidos == 'Νηπιαγωγεία') {
              $type2 = 0;
              $type = 0;
            } elseif ($eidos == 'Δημοτικά Σχολεία') {
              $type2 = 0;
              $type = 1;
            } elseif ($eidos == 'Ιδιωτικά Σχολεία') {
              $type2 = 1;
              $type = $typos == 'Ιδιωτικό Δημοτικό Σχολείο' ? 1 : 2;
            }
            if (strstr($typos, 'Ειδικής Αγωγής')) {
              $type2 = 2;
            }

            $dimos = getDimosId($data[6], $mysqlconnection, true);
            
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2,dimos) 
            values('$code','$data[4]',$type,'$data[13]','$data[21]','$data[22]','$data[17]','$data[18]','$data[19]','$data[15]','$data[14]','$type2',$dimos)";
            $imp_8 = safe_iconv_to_utf8($import);

            $update_queries[] = $imp_8;
            $saves++;
            
            break;

          // schools
          case 2:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2) 
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]')";
            $imp_8 = safe_iconv_to_utf8($import);
            
            $update_queries[] = $imp_8;
            $saves++;
            
            break;

          // students ds
          case 3:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // update school set students='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΠΡ-Ζ',tmimata='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΟΛ16,ΠΡ-Ζ' WHERE code='9170117';
            $students = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8]));
            $tm_prz = ceil($data[8]/25);
            $tmimata = implode(',',Array($data[10],$data[11],$data[12],$data[13],$data[14],$data[15],$data[16],$data[17],$tm_prz));
            $entaksis = $data[9] > 0 ? 'on,'.$data[9] : ',';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $students_old = mysqli_result($res, 0, "students");
            $tmimata_old = mysqli_result($res, 0, "tmimata");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $students_old . ',' . $tmimata_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $update_queries[] = safe_iconv_to_utf8($sql);
            
            // update school table
            if ($students <> $students_old || $tmimata <> $tmimata_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET students='$students', tmimata='$tmimata', entaksis='$entaksis' WHERE code=".$data[0];
              
              $update_queries[] = safe_iconv_to_utf8($sql);
              $saves++;
            }
            break;

          // students nip
          case 4:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }

            // update school set klasiko='1Π,1Ν,2Π,2Ν,3Π,3Ν,ΠΖ', oloimero_nip='ΟΛ1Π,ΟΛ1Ν,ΟΛ2Π,ΟΛ2Ν',entaksis='0,0' where code=9170040;
            $klasiko = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]));
            $oloimero_nip = implode(',',Array($data[8],$data[9],$data[10],$data[11]));
            $entaksis = $data[12] > 0 ? 'on,'.$data[12] : '0,0';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $klasiko_old = mysqli_result($res, 0, "klasiko");
            $oloimero_nip_old = mysqli_result($res, 0, "oloimero_nip");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $klasiko_old . ',' . $oloimero_nip_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $update_queries[] = safe_iconv_to_utf8($sql);

            // update school table
            if ($klasiko <> $klasiko_old || $oloimero_nip <> $oloimero_nip_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET klasiko='$klasiko', oloimero_nip='$oloimero_nip', entaksis='$entaksis' WHERE code=".$data[0];
              $update_queries[] = safe_iconv_to_utf8($sql);
              $saves++;
            }
            break;
          // topothetiseis
          // 5: 5) Τοποθετήσεις μονίμων εκπ/κών
          // 6: 6) Τοποθετήσεις μονίμων εκπ/κών με αντικατάσταση τοποθετήσεων  (για αποσπάσεις)
          // 7: 7) Τοποθετήσεις αναπληρωτών εκπ/κών
          case 5:
          case 6:
          case 7:
            // Decide if AM of AFM on 1st column
            $searchcol = strlen($data[0]) > 8 ? 'afm' : 'am';
            $searchcolname = strlen($data[0]) > 8 ? 'ΑΦΜ' : 'ΑΜ';
            // check if $data[0] has a length of 8 characters. If yes, add a leading zero:
            if (strlen($data[0]) == 8) $data[0] = '0'.$data[0];
            $is_mon = $_POST['type'] == 5 || $_POST['type'] == 6 ? true : false;

            // If anaplirotes & am in csv, abort with a message
            if (!$is_mon && $searchcol == 'am'){
              echo "<div class='result-message result-error'>";
              echo "<h3>ΣΦΑΛΜΑ: Δεν είναι δυνατή η εισαγωγή τοποθετήσεων αναπληρωτών με ΑΜ!</h3>";
              echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
              echo "</div>";
              echo "</div>";
              die();
            }
            $delete_yphr = $_POST['type'] == 6 ? true : false;
            // csv monimoi: AM/ΑΦΜ εκπ/κού;Κωδικός ΥΠΑΙΘ σχολείου;Ώρες
            // csv ektaktoi: AM/ΑΦΜ εκπ/κού;Ωράριο;Κωδικός ΥΠΑΙΘ σχολείου;Ώρες
            if ($is_mon) {
              $sch_code = trim($data[1]);
              $hours = trim($data[2]);
            } else {
              $wrario = intval(trim($data[1]));
              $sch_code = trim($data[2]);
              $hours = trim($data[3]);
            }

            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if am/afm exists @ monimoi & ektaktoi
            $emp_qry = $is_mon ? "SELECT * FROM employee WHERE $searchcol = '$data[0]'" : "SELECT * FROM ektaktoi WHERE $searchcol = '$data[0]'";
            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με $searchcolname ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // check school codes
            $sch_id = getSchoolFromCode($sch_code,$mysqlconn);
            if (!$sch_id) {
              $error = true;
              $er_msg = 'Σφάλμα: Δε βρέθηκε το σχολείο με 7ψήφιο κωδικό: ' . $sch_code;
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check hours
            if ($hours <= 0 || $hours > 30) {
              $error = true;
              $er_msg = 'Σφάλμα: Λάθος αριθμός ωρών: ' . $hours;
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check wrario for ektaktoi
            if (!$is_mon && ($wrario <= 0 || $wrario > 30)) {
              $error = true;
              $er_msg = 'Σφάλμα: Λάθος ωράριο αναπληρωτή: ' . $data[1];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            $yp_table = $is_mon ? 'yphrethsh' : 'yphrethsh_ekt';

            if ($delete_yphr) {
              // delete all yphrethseis of employee @ current school year
              $yphr_qry = "DELETE from $yp_table WHERE emp_id = $id AND sxol_etos = $sxol_etos";
              $yphr = mysqli_query($mysqlconnection,$yphr_qry);
            } else {
              // check if yphrethsh already inserted. If yes, skip record.
              $yphr_qry = "SELECT id FROM $yp_table WHERE emp_id = $id AND yphrethsh = $sch_id AND sxol_etos = $sxol_etos";
              $yphr = mysqli_query($mysqlconnection,$yphr_qry);
              if (mysqli_num_rows($yphr) > 0){
                $warnings ++;
                $warn_msg .= '<br>- Η τοποθέτηση υπάρχει ήδη: ';
                $warn_msg .= $emp_row['afm'] . ': '.$emp_row['surname'].' '.$emp_row['name'];
                $warn_msg .= " (γραμμή ".($num+1).")";
                if (!$is_mon && !in_array($data[0], $top_wres, true)) {
                  $upd_wres = "UPDATE ektaktoi SET wres = $wrario WHERE id = $id";
                  $update_queries[] = safe_iconv_to_utf8($upd_wres);
                  $top_wres[] = $data[0];
                }
                continue 2;
              }
            }

            // insert yphrethsh @ employee table
            if (!in_array($data[0], $top_afm, true)){
              if ($is_mon) {
                $upd_qry = "UPDATE employee SET sx_yphrethshs = $sch_id WHERE id = $id";
              } else {
                $upd_qry = "UPDATE ektaktoi SET sx_yphrethshs = $sch_id, wres = $wrario WHERE id = $id";
                $top_wres[] = $data[0];
              }
              $update_queries[] = safe_iconv_to_utf8($upd_qry);
              $top_afm[] = $data[0];
            } 

            if ($is_mon) {
              $sx_organ = $emp_row['sx_organikhs'];
              $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
                values ($id, '$sch_id', '$hours', '$sx_organ', '$sxol_etos')";
            } else {
              $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) 
                values ($id, '$sch_id', '$hours', '$sxol_etos')";
            }
            $update_queries[] = safe_iconv_to_utf8($query);
            $saves++;
            
            break;
          // Import comments 
          // 8) Μαζική προσθήκη σχολίων
          case 8:
            // csv: ΑΜ/ΑΦΜ εκπ/κού;Σχόλιο
            // Decide if AM of AFM on 1st column
            // > 8 cause it may be 8 characters long
            $searchcol = strlen($data[0]) > 8 ? 'afm' : 'am';
            $searchcolname = strlen($data[0]) > 8 ? 'ΑΦΜ' : 'ΑΜ';
            // check if $data[0] has a length of 8 characters. If yes, add a leading zero:
            if (strlen($data[0]) == 8) $data[0] = '0'.$data[0];
            
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if afm exists @ monimoi
            $emp_qry = "SELECT * FROM employee WHERE $searchcol = '$data[0]'";

            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με $searchcolname ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            
            // update employee table
            $upd_qry = "UPDATE employee set comments=concat(comments,'\n".$data[1]."') where $searchcol='".$data[0]."'";
            $saves++;
            $update_queries[] = safe_iconv_to_utf8($upd_qry);
             
            break;
          // Assign praxi to ektaktoi
          // 9) Μαζική ανάθεση αναπληρωτών σε πράξεις
          case 9:
            // csv: ΑΦΜ εκπ/κού;ID πράξης
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if afm exists @ ektaktoi
            $emp_qry = "SELECT * FROM ektaktoi WHERE afm = '$data[0]'";

            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με ΑΦΜ ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            
            // update employee table
            $upd_qry = "UPDATE ektaktoi set praxi=$data[1] where afm='".$data[0]."'";
            $saves++;
            $update_queries[] = safe_iconv_to_utf8($upd_qry);
             
            break;
        }
        if ($error){
          break;
        }
        
        $num++;
      }
      
      fclose($handle);

      if (!$error){
        // execute all update / insert queries
        $queries = implode(';', $update_queries);
        // echo "<br>Queries:<br>".$queries."<br><br>";

        //$ret = mysqli_multi_query($mysqlconnection, $queries);

        // display an image with the queries for debugging
        $qries = htmlspecialchars($queries, ENT_QUOTES, 'UTF-8');
        $infolink = "&nbsp;<img src='../images/info.png' width='20' height='20' title='Queries: $qries' />";

        mysqli_autocommit($mysqlconnection,FALSE);
        $errors = array();
        foreach ( $update_queries as $qry) {
          $res = mysqli_query($mysqlconnection, $qry);
          if (!$res) {
            $errors[$qry] = mysqli_error();
          }
        }
        if (!mysqli_commit($mysqlconnection)){
          echo "<div class='result-message result-error'>";
          echo "<h3>Σφάλμα κατά την εκτέλεση των ενημερώσεων στη βάση</h3>";
          foreach($errors as $k=>$v){
            echo "<br>".htmlspecialchars($k).": ".htmlspecialchars($v);
          }
          echo "<h4>Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.</h4>";
          echo "</div>";
          echo "</div>";
          die();
        }


        // if new employees, add their yphrethseis
        foreach( $update_yphrethseis as $key => $value ) {
          $query = "select * from employee where am = ".$key;
          $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
          $res = mysqli_query($mysqlconn,$query);
          $row = mysqli_fetch_assoc($res);
          $qry = str_replace($key, $row['id'], $value);
          $res = mysqli_query($mysqlconn, $qry);
        }
        // if (!$ret) {
        //   echo "Προέκυψε σφάλμα κατά την εκτέλεση των ενημερώσεων στη Β.Δ...";
        // }
        if ($warnings > 0){
          echo "<div class='result-message result-warning'>";
          echo "<h4>Παρατηρήσεις - προειδοποιήσεις:</h4>";
          echo $warn_msg;
          echo "</div>";
        }
        if ($saves > 0){ 
          echo "<div class='result-message result-success'>";
          echo "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
          echo "<p>Έγινε εισαγωγή <strong>$saves</strong> εγγραφών στον πίνακα <strong>$tbl</strong>.$infolink</p>";
          echo "</div>";
        } else {
          echo "<div class='result-message result-warning'>";
          echo "<h3>Δεν έγινε καμία εισαγωγή στη βάση δεδομένων.$infolink</h3>";
          echo "</div>";
        }
      }
      else
      {
          echo "<div class='result-message result-error'>";
          echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
          
          echo mysqli_error($mysqlconnection) ? "<p><strong>Μήνυμα λάθους:</strong> ".htmlspecialchars(mysqli_error($mysqlconnection))."</p>" : '';
          echo $er_msg ? "<p><strong>$er_msg</strong></p>" : '';
          echo "<p>Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.</p>";
          echo "</div>";
      }
    }
    else {
        require '../etc/menu.php';
        echo "<div class='import-container'>";
        echo "<div class='result-message result-error'>";
        echo "<h3>Σφάλμα: Δεν επιλέξατε αρχείο</h3>";
        echo "</div>";
        echo "</div>";
    }
                
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
    echo "</div>";
    echo "</div>";
?>

</body>
</html>
	