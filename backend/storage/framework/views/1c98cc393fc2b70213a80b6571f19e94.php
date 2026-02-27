<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta Comercial - <?php echo e($order->order_number); ?></title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.4;
            position: relative;
            min-height: 100vh;
        }
        
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            
            pointer-events: none;
        }
        
        .background-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .container {
            position: relative;
            z-index: 1;
            padding: 20px 50px;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            letter-spacing: 2px;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .logo-subtitle {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        
        .main-title {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Top Info */
        .top-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .top-info-left {
            display: table-cell;
            width: 50%;
        }
        
        .top-info-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }
        
        /* About Project */
        .about-project {
            background-color: transparent;
            padding: 10px;
            border-left: 4px solid #2c3e50;
            margin-bottom: 12px;
            font-size: 9px;
            line-height: 1.4;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        
        /* Two Columns Layout */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            page-break-inside: avoid;
            page-break-before: avoid;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
            page-break-inside: avoid;
        }
        
        .column-left {
            padding-right: 10px;
        }
        
        .column-right {
            padding-left: 10px;
        }
        
        /* Section */
        .section {
            margin-bottom: 8px;
            background-color: rgba(44, 62, 80, 0.1);
            padding: 8px;
            border: 2px solid #2c3e50;
        }
        
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 2px;
        }
        
        .section-content {
            font-size: 8px;
            line-height: 1.3;
        }
        
        .section-content p {
            margin-bottom: 4px;
        }
        
        .section-content ul {
            margin-left: 15px;
            margin-bottom: 4px;
        }
        
        .section-content li {
            margin-bottom: 3px;
        }
        
        /* Models List */
        .model-item {
            margin-bottom: 3px;
            padding: 3px;
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        /* Value Display */
        .value-display {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            margin: 3px 0;
        }
        
        .value-large {
            font-size: 11px;
            color: #000;
        }
        
        /* Payment Info */
        .payment-item {
            margin-bottom: 3px;
        }
        
        /* Total Section */
        .total-section {
            background-color: rgba(44, 62, 80, 0.1);
            padding: 8px;
            border: 2px solid #2c3e50;
            margin-top: 6px;
        }
        
        .total-label {
            font-weight: bold;
            font-size: 9px;
        }
        
        .total-value {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 3px;
        }
        
        /* Footer */
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(44, 62, 80, 0.9);
            color: #fff;
            padding: 15px 50px;
            text-align: center;
            font-size: 10px;
            z-index: 2;
        }
        
        .footer-line {
            margin: 3px 0;
        }
        
        /* Notes */
        .notes {
            font-size: 8px;
            font-style: italic;
            color: #666;
            margin-top: 6px;
        }
        
        /* Observations */
        .observations {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 12px;
            margin-top: 20px;
            margin-bottom: 100px;
            border-top: 2px solid #2c3e50;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <?php if(!empty($backgroundImagePath)): ?>
    <div class="background-image">
        <img src="<?php echo e($backgroundImagePath); ?>" alt="Background">
    </div>
    <?php endif; ?>
    
    <div class="container">
        <!-- Header -->
        <div class="header" style="margin-top: 105px;">
            
            <div class="main-title">PROPOSTA COMERCIAL</div>
        </div>
        
        <!-- Top Info -->
        <div class="top-info">
            <div class="top-info-left">
                <div><span class="info-label">Data:  <?php echo e($order->created_at->format('d/m/Y')); ?></span></div>
            </div>
            <div class="top-info-right">
                <div><span class="info-label">Cliente: <?php echo e($order->customer->name); ?></span> </div>
            </div>
        </div>
        
        <!-- About Project -->
        <div class="about-project">
            <strong>SOBRE O PROJETO</strong><br>
            Produção de tenda em suplex de alta qualidade, feita sob medida conforme as especificações do projeto. 
            Indicada para montagem rápida, cenografia de eventos e cobertura de ambientes diversos, fixos ou efêmeros.
        </div>
        
        <!-- Two Columns -->
        <div class="two-columns">
            <!-- Left Column -->
            <div class="column column-left">
                <!-- Modelos -->
                <div class="section">
                    <div class="section-title">MODELOS</div>
                    <div class="section-content">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="model-item">
                            <strong>Tenda:</strong> 
                            <?php if($item->width && $item->height): ?>
                                <?php echo e(number_format($item->width, 0, ',', '.')); ?> × <?php echo e(number_format($item->height, 0, ',', '.')); ?> m
                            <?php else: ?>
                                <?php echo e($item->product_name); ?>

                            <?php endif; ?>
                            <?php if($item->quantity > 1): ?>
                                (<?php echo e($item->quantity); ?> unidades)
                            <?php endif; ?>
                            <br>
                            <?php if($item->area): ?>
                                <strong>Área:</strong> <?php echo e(number_format($item->area * ($item->quantity ?? 1), 2, ',', '.')); ?> m²
                            <?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($calculations['totalArea'] > 0): ?>
                        <div class="value-display" style="margin-top: 6px;">
                            <strong>Área total: <?php echo e(number_format($calculations['totalArea'], 2, ',', '.')); ?> m²</strong>
                        </div>
                        <?php endif; ?>
                        <div class="notes" style="margin-top: 4px;">Conforme imagens na última página</div>
                    </div>
                </div>
                
                <!-- Valor do Produto -->
                <div class="section">
                    <div class="section-title">VALOR DO PRODUTO</div>
                    <div class="section-content">
                        <div class="payment-item">
                            <strong>Valor de venda por m²:</strong> R$ <?php echo e(number_format($calculations['pricePerSquareMeter'], 2, ',', '.')); ?>

                        </div>
                        <div class="value-display value-large" style="margin-top: 5px;">
                            Valor do produto (<?php echo e(number_format($calculations['totalArea'], 2, ',', '.')); ?> m²): 
                            <strong>R$ <?php echo e(number_format($calculations['totalProductValue'], 2, ',', '.')); ?></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Formas de Pagamento -->
                <div class="section">
                    <div class="section-title">FORMAS DE PAGAMENTO</div>
                    <div class="section-content">
                        <div class="payment-item">50% p/ confirmação (R$ <?php echo e(number_format($calculations['totalWithInstallation'] * 0.5, 2, ',', '.')); ?>)</div>
                        <div class="payment-item">50% no ato do envio (R$ <?php echo e(number_format($calculations['totalWithInstallation'] * 0.5, 2, ',', '.')); ?>)</div>
                        <div class="payment-item">Pix ou Cartão (consultar taxas)</div>
                        <div class="payment-item" style="margin-top: 5px;">
                            <strong>Dados do pix:</strong> <?php echo e($company['cnpj']); ?>

                        </div>
                    </div>
                </div>
                
                <!-- Incluso -->
                <div class="section">
                    <div class="section-title">INCLUSO</div>
                    <div class="section-content">
                        <ul>
                            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <?php echo e($item->quantity ?? 1); ?> tenda(s): 
                                <?php if($item->width && $item->height): ?>
                                    <?php echo e(number_format($item->width, 0, ',', '.')); ?> × <?php echo e(number_format($item->height, 0, ',', '.')); ?>m
                                <?php else: ?>
                                    <?php echo e($item->product_name); ?>

                                <?php endif; ?>
                                <?php if($item->area): ?>
                                    (<?php echo e(number_format($item->area, 2, ',', '.')); ?>m²)
                                <?php endif; ?>
                            </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <li>Guia de montagem e suporte a distância</li>
                            <li>Nota fiscal do produto</li>
                            <li>Contatos de clientes p/ referência em todo o Brasil</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="column column-right">
                <!-- Instalação -->
                <?php if($calculations['installationCost'] > 0): ?>
                <div class="section">
                    <div class="section-title">INSTALAÇÃO</div>
                    <div class="section-content">
                        <div class="value-display">
                            Instalação por equipe especializada: <strong>R$ <?php echo e(number_format($calculations['installationCost'], 2, ',', '.')); ?></strong>
                        </div>
                        <div class="notes">
                            Obs: No fechamento da instalação, o frete não é cobrado.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Valor do Frete -->
                <?php if($calculations['freightCost'] > 0): ?>
                <div class="section">
                    <div class="section-title">VALOR DO FRETE</div>
                    <div class="section-content">
                        <div class="value-display">
                            <strong>Valor:</strong> R$ <?php echo e(number_format($calculations['freightCost'], 2, ',', '.')); ?>

                        </div>
                        <?php if($order->customer->city): ?>
                        <div style="margin-top: 3px;">
                            <strong>Cidade de envio:</strong> <?php echo e($order->customer->city); ?>

                            <?php if($order->customer->state): ?>
                                - <?php echo e($order->customer->state); ?>

                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Custos Adicionais -->
                <?php if($calculations['otherCosts'] != 0): ?>
                <div class="section">
                    <div class="section-title">CUSTOS ADICIONAIS</div>
                    <div class="section-content">
                        <?php $__currentLoopData = $order->costs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $costName = strtolower($cost->costType->name ?? '');
                                $costSlug = strtolower($cost->costType->slug ?? '');
                                $isInstallation = str_contains($costName, 'instalação') || str_contains($costSlug, 'instalacao') || 
                                                  str_contains($costName, 'instalacao') || str_contains($costSlug, 'instalacao');
                                $isFreight = str_contains($costName, 'frete') || str_contains($costSlug, 'frete');
                            ?>
                            <?php if(!$isInstallation && !$isFreight): ?>
                            <div class="payment-item">
                                <strong><?php echo e($cost->costType->name ?? 'Custo'); ?>:</strong> 
                                R$ <?php echo e(number_format($cost->value, 2, ',', '.')); ?>

                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="value-display" style="margin-top: 4px;">
                            <strong>Total de custos:</strong> R$ <?php echo e(number_format($calculations['otherCosts'], 2, ',', '.')); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Prazos e Observações -->
                <div class="section">
                    <div class="section-title">PRAZOS E OBSERVAÇÕES</div>
                    <div class="section-content">
                        <div class="payment-item"><strong>Produção:</strong> 5 a 8 dias após confirmação</div>
                        <div class="payment-item"><strong>Prazo de Envio:</strong> de 1 a 3 dias úteis</div>
                        <div class="notes" style="margin-top: 6px;">
                            Produções sob encomenda.<br>
                            Confirmação antecipada p/ garantir prazo.
                        </div>
                    </div>
                </div>
                
                <!-- Valor Total -->
                <div class="section">
                    <div class="section-title">VALOR TOTAL</div>
                    <div class="section-content">
                        <?php if($calculations['installationCost'] > 0): ?>
                        <div class="total-label">Com Instalação:</div>
                        <div class="total-value">R$ <?php echo e(number_format($calculations['totalWithInstallation'], 2, ',', '.')); ?></div>
                        <?php endif; ?>
                        <div class="total-label" style="margin-top: 6px;">
                            <?php if($calculations['installationCost'] > 0): ?>
                                Sem instalação + frete:
                            <?php else: ?>
                                Total:
                            <?php endif; ?>
                        </div>
                        <div class="total-value">R$ <?php echo e(number_format($calculations['totalWithoutInstallation'], 2, ',', '.')); ?></div>
                        <?php if($calculations['installationCost'] > 0): ?>
                        <div class="notes" style="margin-top: 6px;">
                            Obs: Para confirmação sem instalação, não incluimos material de montagem (cordas)
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observações Gerais -->
        <?php if($order->notes): ?>
        <div class="observations">
            <strong>OBSERVAÇÕES GERAIS:</strong><br>
            <?php echo e($order->notes); ?>

        </div>
        <?php endif; ?>
        
        
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/pdf/proposal.blade.php ENDPATH**/ ?>