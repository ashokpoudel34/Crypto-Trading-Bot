<!-- resources/views/dashboard.blade.php -->

<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            
            <?php if(session('status')): ?>
                <div class="mb-4 font-medium text-sm text-green-600">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            
            <h4 class="text-lg font-semibold mb-3">Portfolio Summary</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <h5 class="font-medium">Total Value</h5>
                    <p class="text-xl">$<?php echo e(number_format($totalValue, 2)); ?></p>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg shadow">
                    <h5 class="font-medium">Profit/Loss</h5>
                    <p class="text-xl <?php if($totalProfitLoss >= 0): ?> text-green-600 <?php else: ?> text-red-600 <?php endif; ?>">
                        $<?php echo e(number_format($totalProfitLoss, 2)); ?>

                    </p>
                </div>
            </div>

            
            <h4 class="text-lg font-semibold mb-3">Your Holdings</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Coin</th>
                            <th class="px-4 py-2 text-left font-semibold">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold">Avg Buy Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Current Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Value</th>
                            <th class="px-4 py-2 text-left font-semibold">P/L</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $portfolio; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo e($item->coin->name); ?> (<?php echo e($item->coin->symbol); ?>)</td>
                                <td class="px-4 py-2"><?php echo e(number_format($item->amount, 4)); ?></td>
                                <td class="px-4 py-2">$<?php echo e(number_format($item->average_buy_price, 6)); ?></td>
                                <td class="px-4 py-2">$<?php echo e(number_format($item->current_price, 6)); ?></td>
                                <td class="px-4 py-2">$<?php echo e(number_format($item->current_value, 2)); ?></td>
                                <td class="px-4 py-2 <?php if($item->profit_loss >= 0): ?> text-green-600 <?php else: ?> text-red-600 <?php endif; ?>">
                                    $<?php echo e(number_format($item->profit_loss, 2)); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <h4 class="text-lg font-semibold mb-3">Recent Transactions</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Date</th>
                            <th class="px-4 py-2 text-left font-semibold">Type</th>
                            <th class="px-4 py-2 text-left font-semibold">Coin</th>
                            <th class="px-4 py-2 text-left font-semibold">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold">Price</th>
                            <th class="px-4 py-2 text-left font-semibold">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo e($tx->created_at->format('m/d/Y H:i')); ?></td>
                                <td class="px-4 py-2 <?php if($tx->type == 'buy'): ?> text-green-600 <?php else: ?> text-red-600 <?php endif; ?>">
                                    <?php echo e(strtoupper($tx->type)); ?>

                                </td>
                                <td class="px-4 py-2"><?php echo e($tx->coin->name); ?> (<?php echo e($tx->coin->symbol); ?>)</td>
                                <td class="px-4 py-2"><?php echo e(number_format($tx->amount, 4)); ?></td>
                                <td class="px-4 py-2">$<?php echo e(number_format($tx->price, 6)); ?></td>
                                <td class="px-4 py-2">$<?php echo e(number_format($tx->value_usd, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH /Users/ashokpoudel/Documents/Cyber Security/crypto-bot/resources/views/dashboard.blade.php ENDPATH**/ ?>