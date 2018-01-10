SELECT 
    e.exi_id,
    e.nom,
    e.prenom,
    e.nait,
    e.mort,
    e.url,
    e.data,
    e.exi_id recid,
    SUM(dt.niveau) + COUNT(DISTINCT dt.doc_id) pertinenceTof,
    SUM(dp.niveau) + COUNT(DISTINCT dp.doc_id) pertinenceParent,
    SUM(dt.niveau) + COUNT(DISTINCT dt.doc_id) + SUM(dp.niveau) + COUNT(DISTINCT dp.doc_id) pertinence,
    COUNT(DISTINCT rU.rapport_id)+COUNT(DISTINCT rV.rapport_id) nbVote,
    SUM(rU.niveau)/COUNT(rU.src_id) pertinencePhoto,
    SUM(rV.niveau)/COUNT(rV.src_id) pertinenceVisage
FROM
    flux_exi e
        INNER JOIN
    flux_rapport r ON r.dst_id = e.exi_id
        AND r.dst_obj = 'exi'
        AND r.src_obj = 'doc'
        INNER JOIN
    flux_doc dt ON dt.doc_id = r.src_id
        LEFT JOIN
    flux_doc dp ON dp.doc_id = 5042
        AND dt.lft BETWEEN dp.lft AND dp.rgt
        LEFT JOIN
    flux_rapport rU ON rU.dst_id = e.exi_id
        AND rU.dst_obj = 'exi'
        AND rU.src_obj = 'doc'
        AND rU.src_id = 5043
        AND rU.pre_obj = 'uti'
        LEFT JOIN
    flux_rapport rV ON rV.dst_id = e.exi_id
        AND rV.dst_obj = 'exi'
        AND rV.src_obj = 'doc'
        AND rV.src_id = 53016
        AND rV.pre_obj = 'uti'
GROUP BY e.exi_id
ORDER BY pertinenceVisage DESC , pertinencePhoto DESC , pertinence DESC , e.nom