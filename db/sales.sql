DROP TABLE IF EXISTS [sales_data];
CREATE TABLE [sales_data] (
[id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
[dish_id] INTEGER  NOT NULL,
[price] FLOAT  NOT NULL,
[quantity] FLOAT  NOT NULL,
[waiter_id] INTEGER NOT NULL,
[timestamp] TIMESTAMP NOT NULL,
[order_id] INTERGER NOT NULL
);

DROP TABLE IF EXISTS [table_info];
CREATE TABLE [table_info] (
[id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
[table_id] INTEGER  NOT NULL,
[persons] INTERGER NOT NULL,
[timestamp] TIMESTAMP NOT NULL
)