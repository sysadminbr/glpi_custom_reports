<?php
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1;

include ("../../../../inc/includes.php");

//titre du rapport dans la liste de selection,  soit en dur ici, soit mettre à jour la variable dans les fichiers de traduction;
$report = new PluginReportsAutoReport(__('statusertask_report_title', 'reports'));

//entidade
$entity = new PluginReportsDropdownCriteria($report, $name="`glpi_entities`.`id`", $tableortype="Entity", $label="Cliente");

//tecnico
$tecnico = new PluginReportsDropdownCriteria($report, $name="`glpi_users`.`id`", $tableortype="User", $label="Responsavel");

//critère de selection;
$date = new PluginReportsDateIntervalCriteria($report, '`glpi_tickettasks`.`date`', __('Tasks created', 'reports'));

$report->displayCriteriasForm();

$display_type = Search::HTML_OUTPUT;

if ($report->criteriasValidated()) {
 //  $report->setSubNameAuto();
//   $title    = $report->getFullTitle();

   $cols = [new PluginReportsColumn('realname', __('User')),
            new PluginReportsColumn('date', __('Date')),
            new PluginReportsColumn('ticketid', __('Ticket')),
            new PluginReportsColumn('ticketname', __('Title')),
            new PluginReportsColumn('content', __('Description')),
            new PluginReportsColumn('duree', __('Duration'))
];

   $report->setColumns($cols);


   $query = "SELECT DATE_FORMAT(`glpi_tickettasks`.`date`, '%d/%m/%Y') AS date,
                    `glpi_users`.`firstname` as realname,
                    CONCAT('<a href=\"http://glpi.citrait.com.br/front/ticket.form.php?id=',`glpi_tickets`.`id`,'\">',`glpi_tickets`.`id`,'</a>') AS ticketid,
                    `glpi_tickets`.`name` AS ticketname,
                    REPLACE(REPLACE(`glpi_tickettasks`.`content`,'&#60;p&#62;',''),'&#60;/p&#62;','') AS content,
                    SEC_TO_TIME( sum( glpi_tickettasks.actiontime ) )  AS duree
              FROM `glpi_tickettasks`
              INNER JOIN  `glpi_users` ON (`glpi_tickettasks`.`users_id` = `glpi_users`.`id`)
              INNER JOIN  `glpi_tickets` ON (`glpi_tickets`.`id` = `glpi_tickettasks`.`tickets_id`)
              INNER JOIN  `glpi_entities` ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`)
              WHERE 1=1 ".
                    $tecnico->getSqlCriteriasRestriction()." ".
                    $date->getSqlCriteriasRestriction()." ". $entity->getSqlCriteriasRestriction()."
              GROUP BY date, realname, ticketid";

   $report->setSqlRequest($query);
   $report->setGroupBy('RTOTAL');

   $report->execute();
}
else {
   Html::footer();
}

