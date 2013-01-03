/*
SELECT node.*
FROM flux_tag AS node,
        flux_tag AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND parent.tag_id = 1811
ORDER BY node.lft;
*/

-- SELECT CONCAT( REPEAT(' ', COUNT(parent.code) - 1), node.code) AS name, node.tag_id
 SELECT GROUP_CONCAT(node.code) AS code, GROUP_CONCAT(node.desc) AS lib
-- SELECT node.code, node.desc
FROM flux_tag AS node,
        flux_tag AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.tag_id = 1964
GROUP BY parent.tag_id
ORDER BY node.lft;
