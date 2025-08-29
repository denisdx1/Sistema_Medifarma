

<?php $__env->startSection('title', 'Dashboard BI - Sistema Medifarma'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <!-- Header Fixed at Top -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Dashboard BI - Botica Medifarma
                </h1>
            </div>
            <div class="flex items-center space-x-3">
                <a href="<?php echo e(route('market-management.index')); ?>" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
                <button onclick="openInNewWindow()" 
                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Nueva Ventana
                </button>
            </div>
        </div>
    </div>

    <!-- Full Width Power BI Dashboard -->
    <div class="w-full h-screen" style="height: calc(100vh - 80px);">
        <iframe 
            title="Dashboard Botica Medifarma" 
            width="100%" 
            height="100%" 
            src="https://app.powerbi.com/reportEmbed?reportId=19593871-ae14-4914-8287-86d8360d53e9&autoAuth=true" 
            frameborder="0" 
            allowFullScreen="true"
            style="width: 100%; height: 100%; border: none;">
        </iframe>
    </div>
</div>

<script>
function openInNewWindow() {
    const url = 'https://app.powerbi.com/reportEmbed?reportId=19593871-ae14-4914-8287-86d8360d53e9&autoAuth=true';
    const windowFeatures = 'width=1200,height=800,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no';
    window.open(url, 'PowerBIDashboard', windowFeatures);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/dashboard-bi.blade.php ENDPATH**/ ?>