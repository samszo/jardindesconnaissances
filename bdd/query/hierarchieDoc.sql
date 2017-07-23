 SELECT node.titre, node.url, node.doc_id, node.niveau, node.parent
-- SELECT node.code, node.desc
FROM flux_doc AS node,
        flux_doc AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
-- and node.titre Like "%le général de Gaulle décore la ville de la Croix de la Libération%"
-- and node.niveau = 7
group by node.doc_id
ORDER BY node.lft;
