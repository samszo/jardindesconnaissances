SELECT dp.doc_id, dp.titre, dp.url ,
count(de.doc_id) nbTof 

,	DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d') temps
,	MIN(UNIX_TIMESTAMP(DATE(dgprDeb.valeur))) MinDate
,	MAX(UNIX_TIMESTAMP(DATE(dgprDeb.valeur))) MaxDate


FROM flux_doc dp
inner join flux_doc de on de.parent = dp.doc_id
inner join flux_rapport dgprDeb on dgprDeb.src_id = dp.doc_id AND dgprDeb.src_obj = 'doc' AND dgprDeb.dst_id = 4 AND dgprDeb.dst_obj = 'tag'


group by dp.doc_id, temps