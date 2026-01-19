{{ '{% if message %}<div class="message">{{ message }}</div>{% endif %}' }}
<h2>Add {{ Model }}</h2>
<form action="" method="POST">
	{{ '{{ form.render()|raw }}' }}
	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Add {{ Model }}</button>
		<button type="button" class="btn" onclick="window.location = '/{{ Name }}'">Back to List</button>
	</div>
</form>
