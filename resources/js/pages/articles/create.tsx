import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import AsyncSelect from 'react-select/async';
import AppLayout from '@/layouts/app-layout';
import { Textarea } from '@headlessui/react';
import Select from 'react-select';

export default function ArticleCreate({ tags }: { tags: { label: string, value: string }[] }) {
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [city, setCity] = useState<{ label: string; value: string; lat: number; lon: number } | null>(null);
  const [selectedTags, setSelectedTags] = useState<{ label: string, value: string }[]>([]);

  const loadCities = async (inputValue: string) => {
    if (!inputValue) return [];
    const res = await fetch(
      `https://nominatim.openstreetmap.org/search?city=${encodeURIComponent(inputValue)}&countrycodes=pl&format=json&limit=10`
    );
    const data = await res.json();
    return data.map((c: any) => ({
      label: c.display_name,
      value: c.display_name,
      lat: parseFloat(c.lat),
      lon: parseFloat(c.lon),
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!city) return;
    router.post('/articles', {
      title,
      content,
      city_name: city.value,
      lat: city.lat,
      lon: city.lon,
      tags: selectedTags.map(t => t.value),
    });
  };

  return (
      <AppLayout>
          <form onSubmit={handleSubmit} className="max-w-xl mx-auto p-6 space-y-4">
              <h1 className="text-2xl font-bold mb-4">Dodaj artykuł</h1>
              <Input placeholder="Tytuł" value={title} onChange={e => setTitle(e.target.value)} required />
              <Textarea
                  className="w-full border rounded p-2"
                  placeholder="Treść"
                  value={content}
                  onChange={e => setContent(e.target.value)}
                  required
              />
              <AsyncSelect
                  cacheOptions
                  loadOptions={loadCities}
                  onChange={setCity}
                  placeholder="Wybierz miasto"
                  isClearable
                  styles={{
                      menu: (provided) => ({
                          ...provided,
                          backgroundColor: 'var(--background)',
                          color: 'var(--foreground)',
                      }),
                      option: (provided, state) => ({
                          ...provided,
                          backgroundColor: state.isFocused ? 'var(--accent)' : 'var(--background)',
                          color: 'var(--foreground)',
                      }),
                      control: (provided) => ({
                          ...provided,
                          backgroundColor: 'var(--background)',
                          color: 'var(--foreground)',
                      }),
                      singleValue: (provided) => ({
                          ...provided,
                          color: 'var(--foreground)',
                      }),
                      multiValue: (provided) => ({
                          ...provided,
                          backgroundColor: 'var(--muted)',
                          color: 'var(--foreground)',
                      }),
                  }}
              />
              <Select
                  isMulti
                  options={tags}
                  value={selectedTags}
                  onChange={setSelectedTags}
                  placeholder="Wybierz tagi"
                  className="w-full"
              />
              <Button type="submit" disabled={!city}>Dodaj artykuł</Button>
          </form>
      </AppLayout>

  );
}
