SELECT dp.doc_id, dp.titre, dp.url ,
count(de.doc_id) nbTof 
FROM flux_doc dp
inner join flux_doc de on de.parent = dp.doc_id
group by dp.doc_id