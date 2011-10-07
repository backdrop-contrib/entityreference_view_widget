This module provides an advanced Entity Reference widget that uses a view for selecting items.
The view can be paginated and have exposed filters.
It degrades, so it can be used even if Javascript is disabled.

Usage:
1) Create a view of the entity type you want to reference.
2) If you've added exposed filters, select "Entity Reference View Widget" as the
"Exposed form style" (third column in the View UI). This will allow the exposed
filters to work inside the widget.
3) Under "Format", select Show: Entity, and select "Entity Reference View Widget"
as the View mode. Since the module adds its own view mode, you can control which fields appear,
and in which order, in the Field UI for the entity type you're referencing.
4) Save the View.
5) In the Field UI for the Entity Reference field select "View" as the widget
and on the next page select the View you've created from the dropdown.

The module has a way of hiding selected items from the View.
Simply add a base field contextual argument (Product ID for products, Node ID for nodes, etc)
and in the "More" fieldset enable "Allow multiple values" and "Exclude".
Then edit the Entity Reference field, and in the widget settings enable "Pass selected entity ids to View ".
