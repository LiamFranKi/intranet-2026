{{ '{\% if message \%}<div class="message">{{ message }}</div>{\% endif \%}' }}
<h2>Edit {{ Model }}</h2>
<form action="/{{ Name|lower }}/edit" method="POST">
	{{ '{{ form.render()|raw }}' }}
	{{ '{{ form.id|raw }}' }}
	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Edit {{ Model }}</button>
		<button type="button" class="btn" onclick="window.location = '/{{ Name }}'">Back to List</button>
	</div>
</form>
