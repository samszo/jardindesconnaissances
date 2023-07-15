SELECT count(*) nb, value 
FROM `value` WHERE `property_id` = 3 
group by value
ORDER BY nb DESC